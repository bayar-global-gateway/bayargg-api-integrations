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

## Contoh Handler PHP

```php
<?php

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$invoiceId = $payload['invoice_id'] ?? '';
$status = $payload['status'] ?? '';

if ($invoiceId === '' || $status !== 'paid') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Ignored']);
    exit;
}

// 1. Cari order Anda berdasarkan invoice_id.
// 2. Jika order sudah paid, balas OK tanpa proses ulang.
// 3. Jika belum paid, update status dan kirim produk/layanan.

http_response_code(200);
echo json_encode(['success' => true]);
```

## Contoh Handler Node.js / Express

```js
import express from 'express';

const app = express();
app.use(express.json());

app.post('/webhook/bayar-gg', async (req, res) => {
  const payload = req.body;
  const invoiceId = payload.invoice_id;

  if (!invoiceId || payload.status !== 'paid') {
    return res.json({ success: true, message: 'Ignored' });
  }

  // 1. Cari order berdasarkan invoiceId.
  // 2. Abaikan jika sudah paid.
  // 3. Update order dan jalankan fulfilment.

  return res.json({ success: true });
});

app.listen(3000);
```

## Contoh Handler Python / Flask

```python
from flask import Flask, jsonify, request

app = Flask(__name__)

@app.post("/webhook/bayar-gg")
def bayar_gg_webhook():
    payload = request.get_json(silent=True) or {}
    invoice_id = payload.get("invoice_id")

    if not invoice_id or payload.get("status") != "paid":
        return jsonify({"success": True, "message": "Ignored"})

    # 1. Cari order berdasarkan invoice_id.
    # 2. Abaikan jika sudah paid.
    # 3. Update order dan jalankan fulfilment.

    return jsonify({"success": True})
```

## Idempotency

Webhook bisa dikirim lebih dari satu kali dalam beberapa kondisi jaringan. Pastikan handler Anda idempotent.

Contoh aturan:

- `invoice_id` harus unique di database Anda.
- Jika status order sudah `paid`, jangan kirim produk dua kali.
- Simpan raw payload callback untuk audit.

## Verifikasi Tambahan

Jika ingin verifikasi ulang sebelum fulfilment, panggil:

```http
GET https://www.bayar.gg/api/check-payment.php?invoice=PAY-USERNAME-000001
X-API-Key: YOUR_API_KEY_HERE
```

Lanjutkan fulfilment hanya jika respons menyatakan status pembayaran sukses.
