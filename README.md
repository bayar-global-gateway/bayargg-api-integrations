# BAYAR GG API Integrations

Contoh integrasi resmi untuk REST API BAYAR GG menggunakan **PHP**, **Python**, dan **Node.js**. Repository ini dibuat agar developer bisa langsung membuat payment link, cek status pembayaran, membaca riwayat transaksi, memakai QRIS Converter, WhatsApp Store API, dan Top Up Game API dari website atau backend sendiri.

> API production: `https://www.bayar.gg/api`

## Fitur API

- Buat payment link QRIS / metode pembayaran aktif.
- Cek status invoice dan riwayat pembayaran.
- Ambil daftar payment method yang tersedia untuk akun.
- Statistik akun dan status fitur pembayaran.
- QRIS Converter untuk mengubah QRIS statis menjadi dinamis.
- WhatsApp Store order API.
- Top Up Game API untuk Mobile Legends, PUBG, dan Free Fire.
- Callback/webhook pembayaran sukses ke server Anda.

## Fitur Premium BAYAR GG

Beberapa fitur membutuhkan akun Premium aktif atau aktivasi admin sesuai ketentuan BAYAR GG:

| Fitur | Keterangan |
| --- | --- |
| QRIS BAYAR GG | QRIS dinamis per merchant dengan mID sendiri, webhook otomatis, dan settlement provider |
| BRI Merchant QRIS | Hubungkan QRIS BRI merchant sendiri, dana langsung ke rekening BRI |
| GoPay Merchant QRIS | Hubungkan akun GoPay Merchant via OTP, cocok untuk QRIS merchant GoPay |
| OVO Direct Payment | Integrasi OVO untuk auto-matching mutasi pembayaran |
| WhatsApp Store | Bot toko otomatis di WhatsApp dengan katalog, order, invoice, dan tombol interaktif |
| Top Up Game | Produk Mobile Legends, PUBG, dan Free Fire via Top Up Game API |
| VISA Virtual Card | Kartu virtual VISA USD untuk transaksi online global |
| Mastercard Virtual Card | Kartu virtual Mastercard USD untuk subscription, ads, dan online shopping |
| QRIS Converter | Convert QRIS statis menjadi QRIS dinamis dengan nominal |
| Digital Product Delivery | Auto-delivery file, konten tersembunyi, dan foto produk setelah pembayaran |
| Webhook Callback | Callback otomatis untuk integrasi backend website Anda |

Topik GitHub repository ini juga mengikuti fitur-fitur tersebut agar developer mudah menemukan contoh integrasi yang sesuai.

## Struktur Repository

```text
.
├── README.md
├── README.txt
├── .env.example
├── docs
│   ├── endpoints.json
│   └── webhooks.md
└── examples
    ├── php
    │   ├── BayarGgClient.php
    │   └── example.php
    ├── python
    │   ├── bayar_gg_client.py
    │   ├── example.py
    │   └── requirements.txt
    └── nodejs
        ├── bayar-gg-client.mjs
        ├── example.mjs
        └── package.json
```

## Persiapan

1. Login ke dashboard BAYAR GG.
2. Buka menu API / Settings untuk mengambil API key.
3. Simpan API key di environment variable, jangan hardcode di source code.

```bash
export BAYAR_GG_API_KEY="YOUR_API_KEY_HERE"
export BAYAR_GG_BASE_URL="https://www.bayar.gg/api"
```

Authentication menggunakan header:

```http
X-API-Key: YOUR_API_KEY_HERE
Accept: application/json
```

## Quick Start

### PHP

```bash
cd examples/php
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php example.php
```

### Python

```bash
cd examples/python
python3 -m pip install -r requirements.txt
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 example.py
```

### Node.js

```bash
cd examples/nodejs
npm install
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node example.mjs
```

## Contoh Membuat Payment

Endpoint:

```http
POST https://www.bayar.gg/api/create-payment.php
```

Payload:

```json
{
  "amount": 10000,
  "description": "Order #INV-001",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "customer_phone": "6281234567890",
  "payment_method": "qris",
  "callback_url": "https://example.com/webhook/bayar-gg",
  "redirect_url": "https://example.com/thank-you"
}
```

cURL:

```bash
curl -X POST "https://www.bayar.gg/api/create-payment.php" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "amount": 10000,
    "description": "Order #INV-001",
    "customer_name": "Budi",
    "customer_email": "budi@example.com",
    "customer_phone": "6281234567890",
    "payment_method": "qris",
    "callback_url": "https://example.com/webhook/bayar-gg",
    "redirect_url": "https://example.com/thank-you"
  }'
```

Respons sukses umumnya berisi data invoice, nominal final, dan URL pembayaran. Simpan `invoice_id` di database aplikasi Anda untuk pengecekan berikutnya.

## Contoh Cek Status Payment

```bash
curl "https://www.bayar.gg/api/check-payment.php?invoice_id=PAY-USERNAME-000001" \
  -H "Accept: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE"
```

Gunakan endpoint ini untuk polling ringan jika callback belum diterima.

## Endpoint Utama

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `POST` | `/create-payment.php` | Membuat payment link baru |
| `GET` | `/check-payment.php` | Cek status invoice |
| `GET` | `/list-payments.php` | Riwayat transaksi |
| `GET` | `/get-payment-methods.php` | Daftar metode pembayaran aktif |
| `GET` | `/get-account-status.php` | Status akun dan fitur |
| `GET` | `/get-statistics.php` | Statistik pembayaran |
| `POST` | `/qris-convert.php` | Convert QRIS statis ke dinamis |
| `GET` | `/qris-info.php` | Decode informasi QRIS |
| `GET` | `/wa-store-orders.php` | List order WhatsApp Store |
| `POST` | `/wa-store-complete.php` | Tandai order WhatsApp Store selesai |
| `GET` | `/topup-game/products.php` | List produk top up game |
| `POST` | `/topup-game/order.php` | Buat order top up game |
| `GET` | `/topup-game/status.php` | Cek status order top up |

Detail machine-readable tersedia di `docs/endpoints.json`.

## Payment Methods

Gunakan endpoint berikut untuk membaca metode pembayaran yang aktif di akun Anda:

```http
GET /get-payment-methods.php
```

Nilai `payment_method` yang umum:

- `qris`
- `qris_user`
- `qris_bayar_gg`
- `gopay_qris`
- `ovo`

Ketersediaan metode tergantung status akun, koneksi merchant, dan pengaturan fitur di dashboard.

## Callback / Webhook

Set `callback_url` saat membuat payment. Ketika pembayaran sukses, BAYAR GG akan mengirim request ke URL tersebut.

Contoh payload callback:

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

Rekomendasi handler webhook:

- Terima hanya HTTPS.
- Validasi `invoice_id` ke database Anda.
- Jalankan proses secara idempotent, jangan proses invoice yang sudah pernah diproses.
- Balas HTTP `200 OK` setelah berhasil menerima payload.
- Jika perlu, panggil `GET /check-payment.php` untuk verifikasi ulang ke BAYAR GG.

Lihat detail tambahan di `docs/webhooks.md`.

## Integrasi Top Up Game

Base halaman publik Top Up:

```text
https://topup.bayar.gg
```

Ambil produk:

```bash
curl "https://www.bayar.gg/api/topup-game/products.php?game=ml" \
  -H "Accept: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE"
```

Buat order:

```json
{
  "game": "ml",
  "user_id_game": "12345678",
  "server_id": "1234",
  "product_code": "MLBB3",
  "payment_method": "qris",
  "customer_name": "Budi",
  "customer_phone": "6281234567890"
}
```

Cek status:

```http
GET /topup-game/status.php?order_number=TOPUP-000001
```

## Error Handling

Format error umum:

```json
{
  "success": false,
  "error": "Invalid API key"
}
```

Status yang perlu ditangani:

- `401`: API key kosong atau tidak valid.
- `403`: fitur belum aktif atau akun belum memenuhi syarat.
- `404`: invoice/order tidak ditemukan.
- `405`: HTTP method salah.
- `429`: rate limit.
- `500`: gangguan server, retry dengan jeda.

## Checklist Integrasi Website

- Simpan API key di environment variable.
- Buat endpoint server-side untuk membuat payment.
- Simpan `invoice_id`, `amount`, `final_amount`, dan status di database Anda.
- Redirect user ke `payment_url`.
- Siapkan webhook `callback_url`.
- Saat webhook masuk, verifikasi invoice dan update status order.
- Tambahkan fallback cek status via `/check-payment.php`.
- Jangan pernah expose API key di frontend JavaScript.

## Keamanan

- Jangan commit `.env` atau API key asli.
- Gunakan HTTPS untuk `callback_url` dan `redirect_url`.
- Batasi akses admin integrasi Anda.
- Buat log callback untuk audit.
- Gunakan idempotency berbasis `invoice_id` agar tidak double proses.

## Support

- Website: `https://www.bayar.gg`
- API Docs: `https://www.bayar.gg/api-docs`
- Top Up: `https://topup.bayar.gg`
- Email: `support@bayar.gg`
