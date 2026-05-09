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

        default:
            printHelp();
            throw new InvalidArgumentException('Unknown command: ' . $command);
    }
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

Examples:
  php examples/cli/bayar-gg-cli.php methods
  php examples/cli/bayar-gg-cli.php create-payment --amount=10000 --description="Order #1001" --payment-method=qris
  php examples/cli/bayar-gg-cli.php check-payment --invoice=PAY-USERNAME-000001

TXT;
}
