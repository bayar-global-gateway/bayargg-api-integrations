BAYAR GG API Integrations
=========================

Repository contoh integrasi REST API BAYAR GG untuk PHP, Python, dan Node.js.

Base URL:
  https://www.bayar.gg/api

Authentication:
  Kirim API key di header:
    X-API-Key: YOUR_API_KEY_HERE

Contoh yang tersedia:
  examples/php
    BayarGgClient.php
    example.php

  examples/python
    bayar_gg_client.py
    example.py

  examples/nodejs
    package.json
    bayar-gg-client.mjs
    example.mjs

Dokumentasi endpoint ringkas:
  docs/endpoints.json

Endpoint utama:
  POST /create-payment.php
  GET  /check-payment.php
  GET  /list-payments.php
  GET  /get-payment-methods.php
  GET  /get-account-status.php
  GET  /get-statistics.php
  POST /qris-convert.php
  GET  /qris-info.php
  GET  /wa-store-orders.php
  POST /wa-store-complete.php
  GET  /topup-game/products.php
  POST /topup-game/order.php
  GET  /topup-game/status.php

Cara pakai cepat:
  1. Ambil API key dari dashboard BAYAR GG.
  2. Set environment variable BAYAR_GG_API_KEY.
  3. Jalankan contoh sesuai bahasa.

PHP:
  cd examples/php
  BAYAR_GG_API_KEY=YOUR_API_KEY_HERE php example.php

Python:
  cd examples/python
  BAYAR_GG_API_KEY=YOUR_API_KEY_HERE python3 example.py

Node.js:
  cd examples/nodejs
  npm install
  BAYAR_GG_API_KEY=YOUR_API_KEY_HERE node example.mjs

Catatan keamanan:
  Jangan commit API key asli.
  Selalu validasi callback/webhook di server Anda.
  Gunakan HTTPS untuk callback_url dan redirect_url.
