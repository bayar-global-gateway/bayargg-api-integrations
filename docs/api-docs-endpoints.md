# Endpoint Sesuai API Docs BAYAR GG

File ini hanya mencatat endpoint yang muncul di halaman `https://www.bayar.gg/api-docs`.

## Payment

### POST `/api/create-payment.php`

Membuat invoice/payment link baru.

Body utama:

```json
{
  "amount": 10000,
  "description": "Pembayaran Produk A",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "customer_phone": "6281234567890",
  "callback_url": "https://example.com/webhook/bayar-gg",
  "redirect_url": "https://example.com/thank-you",
  "file_id": "",
  "content_id": "",
  "product_image_id": "",
  "payment_method": "qris_bayar_gg",
  "use_qris_converter": false
}
```

Payment method yang umum:

- `qris`
- `qris_bayar_gg`
- `qris_user`
- `gopay_qris`
- `ovo`

### GET `/api/check-payment.php`

Cek status invoice.

Query:

```text
invoice=PAY-admin-1234567890-ABC123
```

Catatan: nama query yang dipakai API Docs adalah `invoice`.

### GET `/api/list-payments.php`

Daftar payment dengan filter.

Query:

```json
{
  "search": "",
  "status": "",
  "payment_method": "",
  "paid_via": "",
  "start_date": "",
  "end_date": "",
  "page": 1,
  "limit": 10
}
```

## Account

### GET `/api/get-payment-methods.php`

Menampilkan payment method yang bisa dipakai akun.

### GET `/api/get-account-status.php`

Menampilkan status akun, koneksi merchant, dan akses fitur.

### GET `/api/get-statistics.php`

Menampilkan statistik payment.

Query:

```json
{
  "period": "month",
  "start_date": "",
  "end_date": ""
}
```

## Digital Products

### GET `/api/list-files.php`

Daftar file digital user.

Query:

```text
active_only=true
```

### GET `/api/list-contents.php`

Daftar hidden content user.

Query:

```text
active_only=true
```

### GET `/api/list-images.php`

Daftar foto produk user.

Query:

```text
active_only=true
```

Ketiga endpoint ini dipakai bersama `create-payment.php` lewat field `file_id`, `content_id`, dan `product_image_id`.

## QRIS Converter

### POST `/api/qris-convert.php`

Convert QRIS statis ke QRIS dinamis dengan nominal.

Body:

```json
{
  "qris": "00020101021126...",
  "nominal": 50000
}
```

### POST `/api/qris-info.php`

Decode informasi QRIS.

Body:

```json
{
  "qris": "00020101021126..."
}
```

## WhatsApp Store

### GET `/api/wa-store-orders.php`

Daftar order WhatsApp Store.

Query:

```json
{
  "order_number": "",
  "status": "",
  "search": "",
  "limit": 50,
  "offset": 0
}
```

### POST `/api/wa-store-complete.php`

Tandai order WhatsApp Store selesai.

Body:

```json
{
  "order_number": "WA260403-XXXX",
  "status": "completed",
  "notify": true
}
```

## Webhook

Webhook callback dikirim ke `callback_url` ketika payment sukses. Detail handler tersedia di `docs/webhooks.md`.
