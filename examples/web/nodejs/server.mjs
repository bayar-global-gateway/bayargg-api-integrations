#!/usr/bin/env node

import http from 'node:http';
import { URLSearchParams } from 'node:url';
import { BayarGgClient } from '../../nodejs/bayar-gg-client.mjs';

const apiKey = process.env.BAYAR_GG_API_KEY || '';
const baseUrl = process.env.BAYAR_GG_BASE_URL || 'https://www.bayar.gg/api';
const port = Number(process.env.PORT || 8082);
const client = apiKey ? new BayarGgClient(apiKey, baseUrl) : null;

const server = http.createServer(async (req, res) => {
  const form = await readForm(req);
  const action = form.get('action') || new URL(req.url, `http://${req.headers.host}`).searchParams.get('action') || '';
  let result = null;
  let error = '';

  if (client && action) {
    try {
      result = await handleAction(action, form);
    } catch (err) {
      error = err.message;
    }
  }

  const body = renderPage(form, result, error);
  res.writeHead(200, {
    'Content-Type': 'text/html; charset=utf-8',
    'Content-Length': Buffer.byteLength(body),
  });
  res.end(body);
});

server.listen(port, '127.0.0.1', () => {
  console.log(`BAYAR GG Node.js web example running at http://127.0.0.1:${port}`);
});

async function readForm(req) {
  if (req.method !== 'POST') return new URLSearchParams();
  const chunks = [];
  for await (const chunk of req) chunks.push(chunk);
  return new URLSearchParams(Buffer.concat(chunks).toString());
}

async function handleAction(action, form) {
  if (action === 'create_payment') {
    return client.createPayment({
      amount: Number(form.get('amount') || 10000),
      description: form.get('description') || 'Test payment from BAYAR GG Node.js web example',
      customer_name: form.get('customer_name') || '',
      customer_email: form.get('customer_email') || '',
      customer_phone: form.get('customer_phone') || '',
      payment_method: form.get('payment_method') || 'qris',
      payment_url: form.get('payment_url') || 'https://www.bayar.gg/pay',
      callback_url: form.get('callback_url') || '',
      redirect_url: form.get('redirect_url') || '',
    });
  }
  if (action === 'check_payment') return client.checkPayment(form.get('invoice') || '');
  if (action === 'payment_methods') return client.getPaymentMethods();
  if (action === 'account_status') return client.getAccountStatus();
  if (action === 'statistics') return client.getStatistics();
  if (action === 'list_payments') {
    return client.listPayments({
      status: form.get('status') || '',
      page: Number(form.get('page') || 1),
      limit: Number(form.get('limit') || 10),
    });
  }
  return { success: false, error: 'Unknown action' };
}

function renderPage(form, result, error) {
  const warning = apiKey ? '' : `<section class="card warning"><strong>API key belum diset.</strong><pre><code>BAYAR_GG_API_KEY=YOUR_API_KEY_HERE node examples/web/nodejs/server.mjs</code></pre></section>`;
  let resultHtml = '';

  if (error) {
    resultHtml = `<section class="card error"><strong>Error</strong><p>${esc(error)}</p></section>`;
  } else if (result) {
    const paymentUrl = result.payment_url || result.data?.payment_url || '';
    const link = paymentUrl ? `<p><a class="button" href="${esc(paymentUrl)}" target="_blank" rel="noopener">Open Payment URL</a></p>` : '';
    resultHtml = `<section class="card success"><h2>Response</h2>${link}<pre><code>${esc(JSON.stringify(result, null, 2))}</code></pre></section>`;
  }

  return `<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BAYAR GG API Node.js Web Example</title>
  <style>${styles()}</style>
</head>
<body>
<main>
  <header>
    <h1>BAYAR GG API Node.js Web Example</h1>
    <p>Demo web Node.js server-side. API key hanya dibaca dari environment variable <code>BAYAR_GG_API_KEY</code>.</p>
  </header>
  ${warning}
  <section class="grid">
    <form class="card" method="post">
      <h2>Create Payment</h2>
      <input type="hidden" name="action" value="create_payment">
      <label>Amount</label><input name="amount" type="number" value="${esc(form.get('amount') || '10000')}">
      <label>Description</label><input name="description" value="${esc(form.get('description') || 'Test payment from Node.js web example')}">
      <label>Payment Method</label>${methodSelect(form.get('payment_method') || 'qris')}
      <label>Customer Name</label><input name="customer_name" value="${esc(form.get('customer_name') || 'Budi')}">
      <label>Customer Email</label><input name="customer_email" value="${esc(form.get('customer_email') || 'budi@example.com')}">
      <label>Customer Phone</label><input name="customer_phone" value="${esc(form.get('customer_phone') || '6281234567890')}">
      <label>Payment URL</label><input name="payment_url" value="${esc(form.get('payment_url') || 'https://www.bayar.gg/pay')}">
      <label>Callback URL</label><input name="callback_url" placeholder="https://example.com/webhook/bayar-gg" value="${esc(form.get('callback_url') || '')}">
      <label>Redirect URL</label><input name="redirect_url" placeholder="https://example.com/thank-you" value="${esc(form.get('redirect_url') || '')}">
      <button type="submit">Create Payment</button>
    </form>
    <div class="card">
      <h2>Quick Actions</h2>
      <form method="post">
        <input type="hidden" name="action" value="check_payment">
        <label>Invoice ID</label><input name="invoice" placeholder="PAY-USERNAME-000001" value="${esc(form.get('invoice') || '')}">
        <button type="submit">Check Payment</button>
      </form>
      <p class="actions">
        <a class="button secondary" href="?action=payment_methods">Payment Methods</a>
        <a class="button secondary" href="?action=account_status">Account Status</a>
        <a class="button secondary" href="?action=statistics">Statistics</a>
      </p>
      <form method="post">
        <input type="hidden" name="action" value="list_payments">
        <label>Status Filter</label>${statusSelect(form.get('status') || '')}
        <label>Limit</label><input name="limit" type="number" value="${esc(form.get('limit') || '10')}">
        <button type="submit">List Payments</button>
      </form>
    </div>
  </section>
  ${resultHtml}
</main>
</body>
</html>`;
}

function methodSelect(selected) {
  return `<select name="payment_method">${['qris', 'qris_bayar_gg', 'qris_user', 'qris_livin', 'gopay_qris', 'ovo'].map((method) => (
    `<option value="${esc(method)}" ${method === selected ? 'selected' : ''}>${esc(method)}</option>`
  )).join('')}</select>`;
}

function statusSelect(selected) {
  return `<select name="status">${['', 'pending', 'paid', 'expired', 'cancelled'].map((status) => (
    `<option value="${esc(status)}" ${status === selected ? 'selected' : ''}>${esc(status || 'All')}</option>`
  )).join('')}</select>`;
}

function esc(value) {
  return String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[char]));
}

function styles() {
  return `:root{color-scheme:dark;--bg:#08111f;--card:#101b2d;--border:#20324d;--text:#edf3ff;--muted:#9fb0c8;--blue:#2f80ff;--red:#ff5c5c;--green:#19c37d}*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--text)}main{width:min(1120px,calc(100% - 32px));margin:32px auto}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:18px;margin-bottom:16px}p{color:var(--muted);line-height:1.6}label{display:block;margin:12px 0 6px;color:var(--muted);font-size:13px;font-weight:700}input,select{width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--border);background:#07101d;color:var(--text)}button,.button{display:inline-flex;margin-top:14px;border:0;border-radius:10px;padding:11px 14px;background:var(--blue);color:#fff;text-decoration:none;font-weight:800;cursor:pointer}.secondary{background:#28374f}.warning{background:rgba(255,197,61,.08)}.error{background:rgba(255,92,92,.08);color:#ffdede}.success{background:rgba(25,195,125,.08)}pre{overflow:auto;padding:16px;border-radius:14px;background:#030712;border:1px solid var(--border)}code{color:#b8d2ff}.actions{display:flex;flex-wrap:wrap;gap:10px}`;
}
