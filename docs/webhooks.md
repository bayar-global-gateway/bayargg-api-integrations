# BAYAR GG Webhook / Callback Guide

Webhook dipakai agar aplikasi Anda menerima notifikasi otomatis ketika payment sukses.

## Cara Mengaktifkan

Kirim `callback_url` saat membuat payment:

```json
{
  "amount": 10000,
  "description": "Order #INV-001",
  "payment_method": "qris",
  "callback_url": "https://example.com/webhook/bayar-gg",
  "redirect_url": "https://example.com/thank-you"
}
```

## Contoh Payload

```json
{
  "invoice_id": "PAY-USERNAME-000001",
  "status": "paid",
  "amount": 10000,
  "final_amount": 10023,
  "payment_method": "qris",
  "paid_via": "qris",
  "paid_at": "2026-05-10 01:00:00",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "customer_phone": "6281234567890"
}
```

Field yang paling penting:

| Field | Keterangan |
| --- | --- |
| `invoice_id` | ID invoice BAYAR GG, simpan sebagai unique key |
| `status` | Status pembayaran, biasanya `paid` untuk callback sukses |
| `amount` | Nominal dasar |
| `final_amount` | Nominal final yang dibayar |
| `payment_method` | Metode yang diminta saat create payment |
| `paid_via` | Metode real yang mengonfirmasi pembayaran |
| `paid_at` | Waktu pembayaran sukses |

## Keamanan: selalu verifikasi ulang sebelum fulfilment

Body callback **tidak ditandatangani**, jadi siapa pun yang tahu `callback_url` Anda bisa mengirim payload palsu. **Jangan pernah** memenuhi order hanya berdasarkan `status` di body. Setelah membaca `invoice_id`, panggil `check-payment` di sisi server memakai API Key Anda, dan lanjutkan fulfilment **hanya** jika API sendiri menyatakan `paid`. Contoh di bawah sudah menerapkan pola aman ini.

## Contoh Handler PHP

```php
<?php

$apiKey = getenv('BAYAR_GG_API_KEY'); // jangan hardcode

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$invoiceId = $payload['invoice_id'] ?? '';
if ($invoiceId === '') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Ignored']);
    exit;
}

// JANGAN percaya $payload['status']. Verifikasi ulang ke BAYAR GG.
$ch = curl_init('https://www.bayar.gg/api/check-payment.php?invoice=' . rawurlencode($invoiceId));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey, 'Accept: application/json'],
    CURLOPT_TIMEOUT => 30,
]);
$verify = json_decode((string) curl_exec($ch), true) ?: [];
curl_close($ch);

if (($verify['status'] ?? '') !== 'paid') {
    http_response_code(202);
    echo json_encode(['success' => true, 'message' => 'Not paid yet']);
    exit;
}

// Aman: status terverifikasi = paid.
// 1. Cari order Anda berdasarkan invoice_id.
// 2. Jika order sudah paid, balas OK tanpa proses ulang (idempoten).
// 3. Jika belum paid, update status dan kirim produk/layanan.

http_response_code(200);
echo json_encode(['success' => true]);
```

## Contoh Handler Node.js / Express

```js
import express from 'express';

const app = express();
app.use(express.json());
const API_KEY = process.env.BAYAR_GG_API_KEY; // jangan hardcode

app.post('/webhook/bayar-gg', async (req, res) => {
  const invoiceId = req.body?.invoice_id;
  if (!invoiceId) {
    return res.json({ success: true, message: 'Ignored' });
  }

  // JANGAN percaya req.body.status. Verifikasi ulang ke BAYAR GG.
  const r = await fetch(
    `https://www.bayar.gg/api/check-payment.php?invoice=${encodeURIComponent(invoiceId)}`,
    { headers: { 'X-API-Key': API_KEY, Accept: 'application/json' } }
  );
  const verify = await r.json().catch(() => ({}));

  if (verify.status !== 'paid') {
    return res.status(202).json({ success: true, message: 'Not paid yet' });
  }

  // Aman: status terverifikasi = paid.
  // 1. Cari order berdasarkan invoiceId.
  // 2. Abaikan jika sudah paid (idempoten).
  // 3. Update order dan jalankan fulfilment.

  return res.json({ success: true });
});

app.listen(3000);
```

## Contoh Handler Python / Flask

```python
import os
import urllib.parse
import urllib.request
import json
from flask import Flask, jsonify, request

app = Flask(__name__)
API_KEY = os.environ["BAYAR_GG_API_KEY"]  # jangan hardcode


@app.post("/webhook/bayar-gg")
def bayar_gg_webhook():
    payload = request.get_json(silent=True) or {}
    invoice_id = payload.get("invoice_id")
    if not invoice_id:
        return jsonify({"success": True, "message": "Ignored"})

    # JANGAN percaya payload["status"]. Verifikasi ulang ke BAYAR GG.
    url = "https://www.bayar.gg/api/check-payment.php?invoice=" + urllib.parse.quote(invoice_id)
    req = urllib.request.Request(url, headers={"X-API-Key": API_KEY, "Accept": "application/json"})
    with urllib.request.urlopen(req, timeout=30) as resp:
        verify = json.loads(resp.read().decode("utf-8"))

    if verify.get("status") != "paid":
        return jsonify({"success": True, "message": "Not paid yet"}), 202

    # Aman: status terverifikasi = paid.
    # 1. Cari order berdasarkan invoice_id.
    # 2. Abaikan jika sudah paid (idempoten).
    # 3. Update order dan jalankan fulfilment.

    return jsonify({"success": True})
```

## Idempotency

Webhook bisa dikirim lebih dari satu kali dalam beberapa kondisi jaringan. Pastikan handler Anda idempotent.

Contoh aturan:

- `invoice_id` harus unique di database Anda.
- Jika status order sudah `paid`, jangan kirim produk dua kali.
- Simpan raw payload callback untuk audit.

## Catatan

Pola verifikasi-ulang di atas adalah cara yang dipakai plugin resmi (mis. WooCommerce): status pembayaran selalu dicek langsung ke `check-payment` memakai API Key sebelum order ditandai lunas. Selain anti-spoof, sebaiknya juga cocokkan `final_amount` dengan total order Anda.
