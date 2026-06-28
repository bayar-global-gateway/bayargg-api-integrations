<?php

require __DIR__ . '/../../php/BayarGgClient.php';

$apiKey = getenv('BAYAR_GG_API_KEY') ?: '';
$baseUrl = getenv('BAYAR_GG_BASE_URL') ?: 'https://www.bayar.gg/api';
$client = $apiKey !== '' ? new BayarGgClient($apiKey, $baseUrl) : null;
$result = null;
$error = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function prettyJson($value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

if ($client && $action !== '') {
    try {
        if ($action === 'create_payment') {
            $result = $client->createPayment([
                'amount' => (int)($_POST['amount'] ?? 10000),
                'description' => trim($_POST['description'] ?? 'Test payment from BAYAR GG PHP web example'),
                'customer_name' => trim($_POST['customer_name'] ?? ''),
                'customer_email' => trim($_POST['customer_email'] ?? ''),
                'customer_phone' => trim($_POST['customer_phone'] ?? ''),
                'payment_method' => trim($_POST['payment_method'] ?? 'qris'),
                'payment_url' => trim($_POST['payment_url'] ?? 'https://www.bayar.gg/pay'),
                'callback_url' => trim($_POST['callback_url'] ?? ''),
                'redirect_url' => trim($_POST['redirect_url'] ?? ''),
            ]);
        } elseif ($action === 'check_payment') {
            $result = $client->checkPayment(trim($_POST['invoice'] ?? $_GET['invoice'] ?? ''));
        } elseif ($action === 'payment_methods') {
            $result = $client->getPaymentMethods();
        } elseif ($action === 'account_status') {
            $result = $client->getAccountStatus();
        } elseif ($action === 'statistics') {
            $result = $client->getStatistics();
        } elseif ($action === 'list_payments') {
            $result = $client->listPayments([
                'status' => trim($_POST['status'] ?? ''),
                'page' => (int)($_POST['page'] ?? 1),
                'limit' => (int)($_POST['limit'] ?? 10),
            ]);
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAYAR GG API PHP Web Example</title>
    <style>
        :root { color-scheme: dark; --bg:#08111f; --card:#101b2d; --border:#20324d; --text:#edf3ff; --muted:#9fb0c8; --blue:#2f80ff; --green:#19c37d; --red:#ff5c5c; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--bg); color: var(--text); }
        main { width: min(1120px, calc(100% - 32px)); margin: 32px auto; }
        header { margin-bottom: 24px; }
        h1 { margin: 0 0 8px; font-size: clamp(28px, 5vw, 42px); }
        p { color: var(--muted); line-height: 1.6; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 18px; }
        label { display: block; margin: 12px 0 6px; font-size: 13px; color: var(--muted); font-weight: 700; }
        input, select { width: 100%; padding: 11px 12px; border-radius: 10px; border: 1px solid var(--border); background: #07101d; color: var(--text); }
        button, .button { margin-top: 14px; display: inline-flex; border: 0; border-radius: 10px; padding: 11px 14px; color: white; background: var(--blue); cursor: pointer; font-weight: 800; text-decoration: none; }
        .button.secondary { background: #28374f; }
        .warning { border-color: rgba(255, 197, 61, .35); background: rgba(255, 197, 61, .08); }
        .error { border-color: rgba(255, 92, 92, .35); background: rgba(255, 92, 92, .08); color: #ffdede; }
        .success { border-color: rgba(25, 195, 125, .35); background: rgba(25, 195, 125, .08); }
        pre { overflow: auto; padding: 16px; border-radius: 14px; background: #030712; border: 1px solid var(--border); }
        code { color: #b8d2ff; }
        .quick-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>BAYAR GG API PHP Web Example</h1>
        <p>Demo PHP server-side. API key hanya dibaca dari environment variable <code>BAYAR_GG_API_KEY</code>.</p>
    </header>

    <?php if (!$client): ?>
        <section class="card warning">
            <strong>API key belum diset.</strong>
            <pre><code>BAYAR_GG_API_KEY=YOUR_API_KEY_HERE php -S 127.0.0.1:8080 -t examples/web/php</code></pre>
        </section>
    <?php endif; ?>

    <section class="grid">
        <form class="card" method="post">
            <h2>Create Payment</h2>
            <input type="hidden" name="action" value="create_payment">
            <label>Amount</label>
            <input name="amount" type="number" value="<?= h($_POST['amount'] ?? '10000') ?>" min="1000">
            <label>Description</label>
            <input name="description" value="<?= h($_POST['description'] ?? 'Test payment from PHP web example') ?>">
            <label>Payment Method</label>
            <select name="payment_method">
                <?php foreach (['qris', 'qris_bayar_gg', 'qris_user', 'qris_livin', 'gopay_qris', 'ovo'] as $method): ?>
                    <option value="<?= h($method) ?>" <?= (($_POST['payment_method'] ?? 'qris') === $method) ? 'selected' : '' ?>><?= h($method) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Customer Name</label>
            <input name="customer_name" value="<?= h($_POST['customer_name'] ?? 'Budi') ?>">
            <label>Customer Email</label>
            <input name="customer_email" value="<?= h($_POST['customer_email'] ?? 'budi@example.com') ?>">
            <label>Customer Phone</label>
            <input name="customer_phone" value="<?= h($_POST['customer_phone'] ?? '6281234567890') ?>">
            <label>Payment URL</label>
            <input name="payment_url" value="<?= h($_POST['payment_url'] ?? 'https://www.bayar.gg/pay') ?>">
            <label>Callback URL</label>
            <input name="callback_url" placeholder="https://example.com/webhook/bayar-gg" value="<?= h($_POST['callback_url'] ?? '') ?>">
            <label>Redirect URL</label>
            <input name="redirect_url" placeholder="https://example.com/thank-you" value="<?= h($_POST['redirect_url'] ?? '') ?>">
            <button type="submit">Create Payment</button>
        </form>

        <div class="card">
            <h2>Quick Actions</h2>
            <form method="post">
                <input type="hidden" name="action" value="check_payment">
                <label>Invoice ID</label>
                <input name="invoice" placeholder="PAY-USERNAME-000001" value="<?= h($_POST['invoice'] ?? '') ?>">
                <button type="submit">Check Payment</button>
            </form>
            <div class="quick-actions">
                <a class="button secondary" href="?action=payment_methods">Payment Methods</a>
                <a class="button secondary" href="?action=account_status">Account Status</a>
                <a class="button secondary" href="?action=statistics">Statistics</a>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="list_payments">
                <label>Status Filter</label>
                <select name="status">
                    <option value="">All</option>
                    <?php foreach (['pending', 'paid', 'expired', 'cancelled'] as $status): ?>
                        <option value="<?= h($status) ?>" <?= (($_POST['status'] ?? '') === $status) ? 'selected' : '' ?>><?= h($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Limit</label>
                <input name="limit" type="number" value="<?= h($_POST['limit'] ?? '10') ?>" min="1" max="100">
                <button type="submit">List Payments</button>
            </form>
        </div>
    </section>

    <?php if ($error): ?>
        <section class="card error" style="margin-top:16px"><strong>Error</strong><p><?= h($error) ?></p></section>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <section class="card success" style="margin-top:16px">
            <h2>Response</h2>
            <?php $payUrl = $result['payment_url'] ?? ($result['data']['payment_url'] ?? ''); ?>
            <?php if (!empty($payUrl)): ?>
                <p><a class="button" href="<?= h($payUrl) ?>" target="_blank" rel="noopener">Open Payment URL</a></p>
            <?php endif; ?>
            <pre><code><?= h(prettyJson($result)) ?></code></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
