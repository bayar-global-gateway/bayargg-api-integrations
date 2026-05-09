# BAYAR GG API Integrations

Contoh integrasi resmi untuk REST API yang tampil di **API Docs BAYAR GG** menggunakan **PHP**, **Python**, dan **Node.js**. Repository ini dibuat agar developer bisa langsung membuat payment link, cek status pembayaran, membaca riwayat transaksi, memakai QRIS Converter, Digital Product API, WhatsApp Store API, dan webhook callback dari website atau backend sendiri.

> API production: `https://www.bayar.gg/api`

## Fitur API

- Buat payment link QRIS / metode pembayaran aktif.
- Cek status invoice dan riwayat pembayaran.
- Ambil daftar payment method yang tersedia untuk akun.
- Statistik akun dan status fitur pembayaran.
- QRIS Converter untuk mengubah QRIS statis menjadi dinamis.
- Digital Product API untuk file, konten, dan foto produk.
- WhatsApp Store order API.
- Callback/webhook pembayaran sukses ke server Anda.

## Scope Repository Ini

Repository ini hanya berisi endpoint yang ada di halaman `https://www.bayar.gg/api-docs`.

Yang termasuk:

- Payment API.
- Account API.
- Digital Products API.
- QRIS Converter API.
- WhatsApp Store API.
- Integration guide untuk OVO, QRIS BAYAR GG, BRI Merchant QRIS, GoPay Merchant QRIS.
- Webhook callback reference.

Yang tidak dimasukkan:

- Endpoint admin internal.
- Endpoint dashboard user internal.
- Endpoint Top Up Game public, karena tidak masuk menu utama API Docs.
- Endpoint kartu virtual admin/internal.

## Fitur API Premium di API Docs

Beberapa fitur API membutuhkan akun Premium aktif, koneksi merchant, atau aktivasi admin sesuai ketentuan BAYAR GG:

| Fitur | Keterangan |
| --- | --- |
| QRIS BAYAR GG | QRIS dinamis per merchant dengan mID sendiri, webhook otomatis, dan settlement provider |
| BRI Merchant QRIS | Hubungkan QRIS BRI merchant sendiri, dana langsung ke rekening BRI |
| GoPay Merchant QRIS | Hubungkan akun GoPay Merchant via OTP, cocok untuk QRIS merchant GoPay |
| OVO Direct Payment | Integrasi OVO untuk auto-matching mutasi pembayaran |
| WhatsApp Store | Bot toko otomatis di WhatsApp dengan katalog, order, invoice, dan tombol interaktif |
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
│   ├── api-docs-endpoints.md
│   ├── endpoints.json
│   ├── tutorial-lengkap.md
│   └── webhooks.md
└── examples
    ├── cli
    │   ├── bayar-gg-cli.php
    │   ├── bayar_gg_cli.py
    │   └── bayar-gg-cli.mjs
    ├── web
    │   ├── php
    │   │   └── index.php
    │   ├── python
    │   │   └── app.py
    │   └── nodejs
    │       ├── package.json
    │       └── server.mjs
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

## Tutorial Lengkap

Jika baru pertama kali integrasi, mulai dari panduan ini:

`docs/tutorial-lengkap.md`

Isi tutorial:

- Alur integrasi checkout dari nol.
- Cara ambil dan menyimpan API key.
- Cara menjalankan web demo PHP, Python, dan Node.js.
- Cara menjalankan CLI.
- Create payment dan redirect ke `payment_url`.
- Setup callback/webhook.
- Cek status sebagai fallback.
- Digital product delivery.
- QRIS Converter.
- WhatsApp Store API.
- Contoh struktur database order.
- Checklist production dan troubleshooting.

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

### Web Version PHP

Contoh web PHP server-side agar API key tidak terekspos ke frontend.

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php -S 127.0.0.1:8080 -t examples/web/php
```

Buka:

```text
http://127.0.0.1:8080
```

### Web Version Python

Contoh web Python memakai standard library `http.server`, tanpa dependency tambahan.

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/web/python/app.py
```

Buka:

```text
http://127.0.0.1:8081
```

### Web Version Node.js

Contoh web Node.js memakai built-in `http` dan `fetch`, tanpa dependency tambahan.

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/web/nodejs/server.mjs
```

Buka:

```text
http://127.0.0.1:8082
```

Fitur semua demo web:

- Create payment.
- Check payment.
- List payment methods.
- Account status.
- Statistics.
- List payments.
- Link langsung ke `payment_url` jika create payment berhasil.

### CLI Version PHP

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php help
```

Contoh:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php methods
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" php examples/cli/bayar-gg-cli.php check-payment \
  --invoice=PAY-USERNAME-000001
```

### CLI Version Python

Install dependency:

```bash
python3 -m pip install -r examples/python/requirements.txt
```

Jalankan:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py --help
```

Contoh:

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py methods
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" python3 examples/cli/bayar_gg_cli.py check-payment \
  --invoice=PAY-USERNAME-000001
```

### CLI Version Node.js

```bash
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs help
```

Contoh:

```bash
# List payment methods
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs methods

# Create payment
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs create-payment \
  --amount=10000 \
  --description="Order #1001" \
  --payment-method=qris \
  --customer-name="Budi" \
  --customer-phone=6281234567890

# Check invoice
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs check-payment \
  --invoice=PAY-USERNAME-000001

# List paid payments
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs list-payments \
  --status=paid \
  --limit=10

# Digital product references
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs files
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs contents
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs images

# WhatsApp Store orders
BAYAR_GG_API_KEY="YOUR_API_KEY_HERE" node examples/cli/bayar-gg-cli.mjs wa-orders --limit=20
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
curl "https://www.bayar.gg/api/check-payment.php?invoice=PAY-USERNAME-000001" \
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
| `GET` | `/list-files.php` | Daftar file digital |
| `GET` | `/list-contents.php` | Daftar hidden content |
| `GET` | `/list-images.php` | Daftar foto produk |
| `POST` | `/qris-convert.php` | Convert QRIS statis ke dinamis |
| `POST` | `/qris-info.php` | Decode informasi QRIS |
| `GET` | `/wa-store-orders.php` | List order WhatsApp Store |
| `POST` | `/wa-store-complete.php` | Tandai order WhatsApp Store selesai |

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
- Email: `support@bayar.gg`
