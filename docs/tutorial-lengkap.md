# Tutorial Lengkap Integrasi API BAYAR GG

Panduan ini menjelaskan alur integrasi dari nol sampai payment berhasil diproses di website Anda.

## 1. Alur Integrasi yang Direkomendasikan

Alur paling aman untuk website:

1. User checkout di website Anda.
2. Backend website Anda memanggil `POST /api/create-payment.php`.
3. Website Anda menyimpan `invoice_id`, `amount`, `final_amount`, dan status awal.
4. User diarahkan ke `payment_url` BAYAR GG.
5. BAYAR GG mengirim callback ke `callback_url` saat pembayaran sukses.
6. Backend Anda validasi invoice, update order, lalu kirim produk/layanan.
7. Jika callback belum masuk, backend Anda boleh melakukan fallback check ke `GET /api/check-payment.php`.

Jangan panggil API BAYAR GG langsung dari frontend/browser user karena API key bisa bocor.

## 2. Ambil API Key

1. Login ke dashboard BAYAR GG.
2. Buka halaman API / Settings.
3. Copy API key.
4. Simpan sebagai environment variable:

```bash
export BAYAR_GG_API_KEY="YOUR_API_KEY_HERE"
export BAYAR_GG_BASE_URL="https://www.bayar.gg/api"
```

## 3. Pilih Contoh Integrasi

Repository ini menyediakan beberapa jenis contoh:

| Jenis | Lokasi | Kegunaan |
| --- | --- | --- |
| PHP SDK-style | `examples/php` | Integrasi backend PHP sederhana |
| Python SDK-style | `examples/python` | Integrasi backend Python sederhana |
| Node.js SDK-style | `examples/nodejs` | Integrasi backend Node.js sederhana |
| CLI PHP | `examples/cli/bayar-gg-cli.php` | Test endpoint dari terminal dengan PHP |
| CLI Python | `examples/cli/bayar_gg_cli.py` | Test endpoint dari terminal dengan Python |
| CLI Node.js | `examples/cli/bayar-gg-cli.mjs` | Test endpoint dari terminal dengan Node.js |
| Web PHP | `examples/web/php` | Demo form web versi PHP |
| Web Python | `examples/web/python` | Demo form web versi Python |
| Web Node.js | `examples/web/nodejs` | Demo form web versi Node.js |

## 4. Menjalankan Web Demo

### PHP

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php -S 127.0.0.1:8080 -t examples/web/php
```

Buka:

```text
http://127.0.0.1:8080
```

### Python

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/web/python/app.py
```

Buka:

```text
http://127.0.0.1:8081
```

### Node.js

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/web/nodejs/server.mjs
```

Buka:

```text
http://127.0.0.1:8082
```

Semua web demo menjalankan request API dari server, bukan dari JavaScript frontend.

## 5. Menjalankan CLI

CLI cocok untuk test cepat sebelum masuk ke code website. Pilih sesuai bahasa yang paling nyaman.

### PHP CLI

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php help
```

Create payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris \
  --customer-name="Budi" \
  --customer-phone=6281234567890
```

Check payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php check-payment \
  --invoice=PAY-USERNAME-000001
```

### Python CLI

```bash
python3 -m pip install -r examples/python/requirements.txt
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py --help
```

Create payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris \
  --customer-name="Budi" \
  --customer-phone=6281234567890
```

Check payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py check-payment \
  --invoice=PAY-USERNAME-000001
```

### Node.js CLI

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs help
```

List payment method:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs methods
```

Create payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris \
  --customer-name="Budi" \
  --customer-phone=6281234567890 \
  --callback-url="https://example.com/webhook/bayar-gg" \
  --redirect-url="https://example.com/thank-you"
```

Check payment:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs check-payment \
  --invoice=PAY-USERNAME-000001
```

## 6. Membuat Payment dari Backend

Endpoint:

```http
POST https://www.bayar.gg/api/create-payment.php
```

Header:

```http
Accept: application/json
Content-Type: application/json
X-API-Key: YOUR_API_KEY_HERE
```

Body minimal:

```json
{
  "amount": 10000,
  "description": "Order #1001",
  "payment_method": "qris"
}
```

Body lengkap:

```json
{
  "amount": 10000,
  "description": "Order #1001",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "customer_phone": "6281234567890",
  "payment_method": "qris",
  "callback_url": "https://example.com/webhook/bayar-gg",
  "redirect_url": "https://example.com/thank-you",
  "file_id": "",
  "content_id": "",
  "product_image_id": "",
  "use_qris_converter": false
}
```

Simpan field penting dari response:

- `invoice_id`
- `amount`
- `final_amount`
- `status`
- `payment_url`
- `expires_at`

## 7. Memilih Payment Method

Cek payment method yang tersedia:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs methods
```

Payment method umum:

| Method | Keterangan |
| --- | --- |
| `qris` | QRIS Admin |
| `qris_bayar_gg` | QRIS BAYAR GG per merchant, butuh aktivasi |
| `qris_user` | BRI Merchant QRIS user |
| `gopay_qris` | GoPay Merchant QRIS |
| `ovo` | OVO Direct Payment |

Jika metode belum aktif, API akan mengembalikan error. Gunakan `get-payment-methods` dan `get-account-status` untuk memeriksa syaratnya.

## 8. Redirect User ke Payment URL

Setelah `create-payment` sukses, redirect user ke `payment_url`.

Contoh PHP:

```php
header('Location: ' . $response['payment_url']);
exit;
```

Contoh Node.js / Express:

```js
res.redirect(response.payment_url || response.data.payment_url);
```

## 9. Setup Callback / Webhook

Saat create payment, isi:

```json
{
  "callback_url": "https://example.com/webhook/bayar-gg"
}
```

Webhook handler harus:

1. Menerima JSON dari BAYAR GG.
2. Membaca `invoice_id`.
3. Cek apakah invoice sudah pernah diproses.
4. Jika belum, update order menjadi paid.
5. Kirim produk atau aktifkan layanan.
6. Balas HTTP `200`.

Contoh payload:

```json
{
  "invoice_id": "PAY-USERNAME-000001",
  "status": "paid",
  "amount": 10000,
  "final_amount": 10023,
  "payment_method": "qris",
  "paid_via": "qris",
  "paid_at": "2026-05-10 01:00:00"
}
```

Detail contoh PHP, Python, dan Node.js tersedia di `docs/webhooks.md`.

## 10. Cek Status sebagai Fallback

Jika user kembali ke website tapi webhook belum diterima, panggil:

```http
GET /api/check-payment.php?invoice=PAY-USERNAME-000001
```

CLI:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs check-payment \
  --invoice=PAY-USERNAME-000001
```

Polling jangan terlalu sering. Gunakan jeda beberapa detik dan hentikan jika status sudah final.

## 11. Digital Product Delivery

Jika Anda menjual file/konten/foto produk dari BAYAR GG, ambil ID item:

```bash
node examples/cli/bayar-gg-cli.mjs files
node examples/cli/bayar-gg-cli.mjs contents
node examples/cli/bayar-gg-cli.mjs images
```

Lalu kirim salah satu field saat create payment:

```json
{
  "file_id": 123,
  "content_id": "",
  "product_image_id": ""
}
```

Gunakan hanya ID milik akun Anda.

## 12. QRIS Converter

Convert QRIS statis ke dinamis:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs qris-convert \
  --qris="00020101021126..." \
  --nominal=50000
```

Decode QRIS info:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs qris-info \
  --qris="00020101021126..."
```

## 13. WhatsApp Store API

List order WhatsApp Store:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs wa-orders --limit=20
```

Tandai order selesai:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs wa-complete \
  --order-number=WA260403-XXXX \
  --status=completed \
  --notify=true
```

## 14. Contoh Database Minimal di Website Anda

Minimal simpan:

```sql
CREATE TABLE orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(64) NOT NULL UNIQUE,
  invoice_id VARCHAR(100) DEFAULT NULL UNIQUE,
  amount DECIMAL(15,2) NOT NULL,
  final_amount DECIMAL(15,2) DEFAULT NULL,
  payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  payment_url TEXT DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Saat create payment:

- Buat order lokal.
- Panggil BAYAR GG.
- Simpan `invoice_id` dan `payment_url`.

Saat webhook:

- Cari order berdasarkan `invoice_id`.
- Jika belum paid, update paid.
- Jika sudah paid, abaikan agar tidak double proses.

## 15. Checklist Sebelum Production

- API key disimpan di environment variable.
- API key tidak pernah tampil di HTML/JavaScript.
- `callback_url` memakai HTTPS.
- Webhook idempotent berdasarkan `invoice_id`.
- Response webhook selalu `200 OK` jika payload valid diterima.
- Log semua callback untuk audit.
- Ada fallback `check-payment`.
- Payment method sudah dicek lewat `/get-payment-methods.php`.
- Error `401`, `403`, `429`, dan `500` ditangani.

## 16. Troubleshooting

### `Invalid API key`

Pastikan:

- Header `X-API-Key` terkirim.
- API key tidak ada spasi di depan/belakang.
- Anda memakai API key dari akun BAYAR GG yang benar.

### `Payment not found`

Pastikan query menggunakan:

```text
invoice=PAY-USERNAME-000001
```

Bukan `invoice_id`.

### Payment method tidak tersedia

Cek:

```bash
node examples/cli/bayar-gg-cli.mjs methods
node examples/cli/bayar-gg-cli.mjs account-status
```

Beberapa metode butuh Premium, koneksi merchant, atau aktivasi admin.

### Webhook tidak masuk

Cek:

- URL webhook bisa diakses publik.
- SSL valid.
- Server membalas HTTP 200.
- Tidak ada firewall yang memblokir request.
- Log raw body webhook di server Anda.

### `429 Too Many Requests`

Kurangi frekuensi request, terutama polling status.

### Response bukan JSON

Pastikan URL API benar:

```text
https://www.bayar.gg/api
```

Dan endpoint sesuai API Docs.

## 17. Urutan Implementasi Tercepat

1. Jalankan CLI `methods`.
2. Jalankan CLI `create-payment`.
3. Buka `payment_url`.
4. Bayar invoice test.
5. Jalankan `check-payment`.
6. Buat webhook handler.
7. Ulangi create payment dengan `callback_url`.
8. Integrasikan ke checkout website.

Dengan urutan ini, developer bisa memastikan API key, payment method, payment URL, status check, dan webhook bekerja satu per satu.
