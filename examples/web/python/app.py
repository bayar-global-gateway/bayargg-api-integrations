#!/usr/bin/env python3

from __future__ import annotations

import html
import json
import os
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import parse_qs, urlencode, urljoin, urlparse
from urllib.request import Request, urlopen


API_KEY = os.getenv("BAYAR_GG_API_KEY", "")
BASE_URL = os.getenv("BAYAR_GG_BASE_URL", "https://www.bayar.gg/api").rstrip("/")


class BayarGgApi:
    def request(self, method: str, path: str, query: dict | None = None, body: dict | None = None) -> dict:
        url = BASE_URL + path
        if query:
            url += "?" + urlencode({k: v for k, v in query.items() if v not in (None, "")})

        payload = None
        headers = {
            "Accept": "application/json",
            "X-API-Key": API_KEY,
        }
        if body is not None:
            payload = json.dumps(body).encode()
            headers["Content-Type"] = "application/json"

        req = Request(url, data=payload, headers=headers, method=method)
        with urlopen(req, timeout=30) as response:
            return json.loads(response.read().decode())


api = BayarGgApi()


class Handler(BaseHTTPRequestHandler):
    def do_GET(self) -> None:
        self.render()

    def do_POST(self) -> None:
        self.render()

    def render(self) -> None:
        form = self.read_form()
        action = form.get("action", [""])[0]
        result = None
        error = ""

        if API_KEY and action:
            try:
                result = self.handle_action(action, form)
            except Exception as exc:
                error = str(exc)

        body = page(form, result, error)
        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Content-Length", str(len(body.encode())))
        self.end_headers()
        self.wfile.write(body.encode())

    def read_form(self) -> dict:
        if self.command == "GET":
            return parse_qs(urlparse(self.path).query)
        length = int(self.headers.get("Content-Length", "0"))
        raw = self.rfile.read(length).decode()
        return parse_qs(raw)

    def handle_action(self, action: str, form: dict) -> dict:
        if action == "create_payment":
            return api.request("POST", "/create-payment.php", body={
                "amount": int(first(form, "amount", "10000")),
                "description": first(form, "description", "Test payment from BAYAR GG Python web example"),
                "customer_name": first(form, "customer_name", ""),
                "customer_email": first(form, "customer_email", ""),
                "customer_phone": first(form, "customer_phone", ""),
                "payment_method": first(form, "payment_method", "qris"),
                "callback_url": first(form, "callback_url", ""),
                "redirect_url": first(form, "redirect_url", ""),
            })
        if action == "check_payment":
            return api.request("GET", "/check-payment.php", query={"invoice": first(form, "invoice", "")})
        if action == "payment_methods":
            return api.request("GET", "/get-payment-methods.php")
        if action == "account_status":
            return api.request("GET", "/get-account-status.php")
        if action == "statistics":
            return api.request("GET", "/get-statistics.php")
        if action == "list_payments":
            return api.request("GET", "/list-payments.php", query={
                "status": first(form, "status", ""),
                "page": first(form, "page", "1"),
                "limit": first(form, "limit", "10"),
            })
        return {"success": False, "error": "Unknown action"}


def first(form: dict, name: str, default: str = "") -> str:
    values = form.get(name)
    return values[0] if values else default


def esc(value: object) -> str:
    return html.escape(str(value), quote=True)


def page(form: dict, result: dict | None, error: str) -> str:
    result_html = ""
    if error:
        result_html = f'<section class="card error"><strong>Error</strong><p>{esc(error)}</p></section>'
    elif result is not None:
        payment_url = result.get("payment_url") or result.get("data", {}).get("payment_url")
        link = f'<p><a class="button" href="{esc(payment_url)}" target="_blank">Open Payment URL</a></p>' if payment_url else ""
        result_html = f'<section class="card success"><h2>Response</h2>{link}<pre><code>{esc(json.dumps(result, indent=2, ensure_ascii=False))}</code></pre></section>'

    warning = ""
    if not API_KEY:
        warning = '<section class="card warning"><strong>API key belum diset.</strong><pre><code>BAYAR_GG_API_KEY=YOUR_API_KEY_HERE python3 examples/web/python/app.py</code></pre></section>'

    return f"""<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BAYAR GG API Python Web Example</title>
  <style>{styles()}</style>
</head>
<body>
<main>
  <header>
    <h1>BAYAR GG API Python Web Example</h1>
    <p>Demo web Python standard library. API key hanya dibaca dari environment variable <code>BAYAR_GG_API_KEY</code>.</p>
  </header>
  {warning}
  <section class="grid">
    <form class="card" method="post">
      <h2>Create Payment</h2>
      <input type="hidden" name="action" value="create_payment">
      <label>Amount</label><input name="amount" type="number" value="{esc(first(form, 'amount', '10000'))}">
      <label>Description</label><input name="description" value="{esc(first(form, 'description', 'Test payment from Python web example'))}">
      <label>Payment Method</label>{method_select(first(form, 'payment_method', 'qris'))}
      <label>Customer Name</label><input name="customer_name" value="{esc(first(form, 'customer_name', 'Budi'))}">
      <label>Customer Email</label><input name="customer_email" value="{esc(first(form, 'customer_email', 'budi@example.com'))}">
      <label>Customer Phone</label><input name="customer_phone" value="{esc(first(form, 'customer_phone', '6281234567890'))}">
      <label>Callback URL</label><input name="callback_url" placeholder="https://example.com/webhook/bayar-gg" value="{esc(first(form, 'callback_url', ''))}">
      <label>Redirect URL</label><input name="redirect_url" placeholder="https://example.com/thank-you" value="{esc(first(form, 'redirect_url', ''))}">
      <button type="submit">Create Payment</button>
    </form>
    <div class="card">
      <h2>Quick Actions</h2>
      <form method="post">
        <input type="hidden" name="action" value="check_payment">
        <label>Invoice ID</label><input name="invoice" placeholder="PAY-USERNAME-000001" value="{esc(first(form, 'invoice', ''))}">
        <button type="submit">Check Payment</button>
      </form>
      <p class="actions">
        <a class="button secondary" href="?action=payment_methods">Payment Methods</a>
        <a class="button secondary" href="?action=account_status">Account Status</a>
        <a class="button secondary" href="?action=statistics">Statistics</a>
      </p>
      <form method="post">
        <input type="hidden" name="action" value="list_payments">
        <label>Status Filter</label>{status_select(first(form, 'status', ''))}
        <label>Limit</label><input name="limit" type="number" value="{esc(first(form, 'limit', '10'))}">
        <button type="submit">List Payments</button>
      </form>
    </div>
  </section>
  {result_html}
</main>
</body>
</html>"""


def method_select(selected: str) -> str:
    methods = ["qris", "qris_bayar_gg", "qris_user", "gopay_qris", "ovo"]
    return "<select name='payment_method'>" + "".join(
        f"<option value='{esc(m)}' {'selected' if m == selected else ''}>{esc(m)}</option>" for m in methods
    ) + "</select>"


def status_select(selected: str) -> str:
    statuses = ["", "pending", "paid", "expired", "cancelled"]
    return "<select name='status'>" + "".join(
        f"<option value='{esc(s)}' {'selected' if s == selected else ''}>{esc(s or 'All')}</option>" for s in statuses
    ) + "</select>"


def styles() -> str:
    return """:root{color-scheme:dark;--bg:#08111f;--card:#101b2d;--border:#20324d;--text:#edf3ff;--muted:#9fb0c8;--blue:#2f80ff;--red:#ff5c5c;--green:#19c37d}*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--text)}main{width:min(1120px,calc(100% - 32px));margin:32px auto}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:18px;margin-bottom:16px}p{color:var(--muted);line-height:1.6}label{display:block;margin:12px 0 6px;color:var(--muted);font-size:13px;font-weight:700}input,select{width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--border);background:#07101d;color:var(--text)}button,.button{display:inline-flex;margin-top:14px;border:0;border-radius:10px;padding:11px 14px;background:var(--blue);color:#fff;text-decoration:none;font-weight:800;cursor:pointer}.secondary{background:#28374f}.warning{background:rgba(255,197,61,.08)}.error{background:rgba(255,92,92,.08);color:#ffdede}.success{background:rgba(25,195,125,.08)}pre{overflow:auto;padding:16px;border-radius:14px;background:#030712;border:1px solid var(--border)}code{color:#b8d2ff}.actions{display:flex;flex-wrap:wrap;gap:10px}"""


if __name__ == "__main__":
    port = int(os.getenv("PORT", "8081"))
    server = HTTPServer(("127.0.0.1", port), Handler)
    print(f"BAYAR GG Python web example running at http://127.0.0.1:{port}")
    server.serve_forever()
