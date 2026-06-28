#!/usr/bin/env php
<?php

require __DIR__ . '/../php/BayarGgClient.php';

$command = $argv[1] ?? 'help';
$args = parseArgs(array_slice($argv, 2));
$apiKey = getenv('BAYAR_GG_API_KEY') ?: ($args['api-key'] ?? $args['apiKey'] ?? '');
$baseUrl = getenv('BAYAR_GG_BASE_URL') ?: ($args['base-url'] ?? $args['baseUrl'] ?? 'https://www.bayar.gg/api');

if ($command !== 'help' && $apiKey === '') {
    fwrite(STDERR, "Missing API key. Set BAYAR_GG_API_KEY or pass --api-key=YOUR_API_KEY_HERE.\n\n");
    printHelp();
    exit(1);
}

$client = new BayarGgClient($apiKey, $baseUrl);

try {
    $result = runCommand($client, $command, $args);
    if ($result !== null) {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

function runCommand(BayarGgClient $client, string $command, array $args): ?array
{
    switch ($command) {
        case 'help':
            printHelp();
            return null;

        case 'methods':
            return $client->getPaymentMethods();

        case 'account-status':
            return $client->getAccountStatus();

        case 'statistics':
            return $client->getStatistics();

        case 'create-payment':
            return $client->createPayment([
                'amount' => (int)($args['amount'] ?? 10000),
                'description' => $args['description'] ?? 'Payment from BAYAR GG PHP CLI',
                'customer_name' => $args['customer-name'] ?? $args['customerName'] ?? '',
                'customer_email' => $args['customer-email'] ?? $args['customerEmail'] ?? '',
                'customer_phone' => $args['customer-phone'] ?? $args['customerPhone'] ?? '',
                'payment_method' => $args['payment-method'] ?? $args['paymentMethod'] ?? 'qris',
                'payment_url' => $args['payment-url'] ?? $args['paymentUrl'] ?? 'https://www.bayar.gg/pay',
                'callback_url' => $args['callback-url'] ?? $args['callbackUrl'] ?? '',
                'redirect_url' => $args['redirect-url'] ?? $args['redirectUrl'] ?? '',
                'file_id' => $args['file-id'] ?? $args['fileId'] ?? '',
                'content_id' => $args['content-id'] ?? $args['contentId'] ?? '',
                'product_image_id' => $args['product-image-id'] ?? $args['productImageId'] ?? '',
                'use_qris_converter' => toBool($args['use-qris-converter'] ?? $args['useQrisConverter'] ?? false),
            ]);

        case 'check-payment':
            requireArg($args, 'invoice');
            return $client->checkPayment($args['invoice']);

        case 'list-payments':
            return $client->listPayments([
                'search' => $args['search'] ?? '',
                'status' => $args['status'] ?? '',
                'payment_method' => $args['payment-method'] ?? $args['paymentMethod'] ?? '',
                'paid_via' => $args['paid-via'] ?? $args['paidVia'] ?? '',
                'start_date' => $args['start-date'] ?? $args['startDate'] ?? '',
                'end_date' => $args['end-date'] ?? $args['endDate'] ?? '',
                'page' => (int)($args['page'] ?? 1),
                'limit' => (int)($args['limit'] ?? 10),
            ]);

        case 'files':
            return $client->listFiles(toBool($args['active-only'] ?? $args['activeOnly'] ?? true));

        case 'contents':
            return $client->listContents(toBool($args['active-only'] ?? $args['activeOnly'] ?? true));

        case 'images':
            return $client->listImages(toBool($args['active-only'] ?? $args['activeOnly'] ?? true));

        case 'qris-convert':
            requireArg($args, 'qris');
            return $client->qrisConvert($args['qris'], (int)($args['nominal'] ?? $args['amount'] ?? 10000));

        case 'qris-info':
            requireArg($args, 'qris');
            return $client->qrisInfo($args['qris']);

        case 'wa-orders':
            return $client->waStoreOrders([
                'order_number' => $args['order-number'] ?? $args['orderNumber'] ?? '',
                'status' => $args['status'] ?? '',
                'search' => $args['search'] ?? '',
                'limit' => (int)($args['limit'] ?? 50),
                'offset' => (int)($args['offset'] ?? 0),
            ]);

        case 'wa-complete':
            $orderNumber = $args['order-number'] ?? $args['orderNumber'] ?? '';
            if ($orderNumber === '') {
                throw new InvalidArgumentException('Missing required option --order-number=...');
            }
            return $client->completeWaStoreOrder(
                $orderNumber,
                $args['status'] ?? 'completed',
                toBool($args['notify'] ?? true)
            );

        case 'merchant-status':
            return $client->merchantStatus();

        case 'merchant-info':
            requireArg($args, 'provider');
            return $client->merchantInfo($args['provider']);

        case 'merchant-balance':
            requireArg($args, 'provider');
            return $client->merchantBalance($args['provider']);

        case 'merchant-history':
            requireArg($args, 'provider');
            return $client->merchantHistory($args['provider'], (int)($args['limit'] ?? 20));

        case 'merchant-set-qris':
            requireArg($args, 'provider');
            return $client->merchantSetQris($args['provider'], (string)($args['qris-string'] ?? $args['qrisString'] ?? ''));

        case 'merchant-disconnect':
            requireArg($args, 'provider');
            return $client->merchantDisconnect($args['provider']);

        case 'merchant-connect':
            requireArg($args, 'provider');
            requireArg($args, 'action');
            return $client->merchantConnect(buildMerchantBody($args));

        default:
            printHelp();
            throw new InvalidArgumentException('Unknown command: ' . $command);
    }
}

function buildMerchantBody(array $args): array
{
    // Petakan opsi CLI → field body accounts-connect (mendukung semua alur connect).
    $map = [
        'provider' => 'provider', 'action' => 'action', 'host' => 'host',
        'username' => 'username', 'password' => 'password', 'mid' => 'mid', 'tid' => 'tid',
        'phone' => 'phone', 'otp' => 'otp', 'pin' => 'pin', 'method' => 'method',
        'connect-token' => 'connect_token', 'outlet-id' => 'outlet_id',
        'account-id' => 'account_id', 'qris-string' => 'qris_string',
    ];
    $body = [];
    foreach ($map as $argKey => $bodyKey) {
        if (isset($args[$argKey]) && $args[$argKey] !== true && $args[$argKey] !== '') {
            $body[$bodyKey] = $args[$argKey];
        }
    }
    return $body;
}

function parseArgs(array $rawArgs): array
{
    $parsed = [];
    foreach ($rawArgs as $arg) {
        if (strpos($arg, '--') !== 0) {
            continue;
        }
        $arg = substr($arg, 2);
        $parts = explode('=', $arg, 2);
        $key = $parts[0];
        $value = $parts[1] ?? true;
        $parsed[$key] = $value;
        $parsed[toCamel($key)] = $value;
    }
    return $parsed;
}

function toCamel(string $key): string
{
    return preg_replace_callback('/-([a-z])/', fn($m) => strtoupper($m[1]), $key);
}

function requireArg(array $args, string $key): void
{
    if (empty($args[$key])) {
        throw new InvalidArgumentException("Missing required option --{$key}=...");
    }
}

function toBool($value): bool
{
    if (is_bool($value)) return $value;
    return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
}

function printHelp(): void
{
    echo <<<TXT
BAYAR GG API PHP CLI

Usage:
  BAYAR_GG_API_KEY=YOUR_API_KEY_HERE php examples/cli/bayar-gg-cli.php <command> [options]

Commands:
  methods                         List payment methods
  account-status                  Get account status
  statistics                      Get payment statistics
  create-payment                  Create payment link
  check-payment --invoice=...     Check payment status
  list-payments                   List payments
  files                           List digital files
  contents                        List hidden contents
  images                          List product images
  qris-convert --qris=...         Convert static QRIS
  qris-info --qris=...            Decode QRIS info
  wa-orders                       List WhatsApp Store orders
  wa-complete --order-number=...  Complete WhatsApp Store order

Merchant API (paket Premium "Semua Fitur"):
  merchant-status                       Status koneksi OVO/BRI/GoPay/Livin
  merchant-info --provider=...          Info akun merchant
  merchant-balance --provider=...       Saldo merchant (ovo/gopay/livin)
  merchant-history --provider=... --limit=20   Riwayat transaksi
  merchant-set-qris --provider=bri|livin --qris-string=...   Set/hapus QRIS statis
  merchant-disconnect --provider=...    Putuskan akun
  merchant-connect --provider=... --action=...  Connect step (lihat API Docs)
      contoh BRI:   merchant-connect --provider=bri --action=connect --host=... --username=... --password=... --mid=... --tid=...
      contoh OVO:   merchant-connect --provider=ovo --action=otp_send --phone=08...
                    merchant-connect --provider=ovo --action=otp_verify --connect-token=cs_xxx --otp=123456
                    merchant-connect --provider=ovo --action=pin --connect-token=cs_xxx --pin=123456

Examples:
  php examples/cli/bayar-gg-cli.php methods
  php examples/cli/bayar-gg-cli.php create-payment --amount=10000 --description="Order #1001" --payment-method=qris --payment-url=https://www.bayar.gg/pay
  php examples/cli/bayar-gg-cli.php check-payment --invoice=PAY-USERNAME-000001

TXT;
}
