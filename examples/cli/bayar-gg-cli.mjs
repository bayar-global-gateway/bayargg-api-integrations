#!/usr/bin/env node

import { BayarGgClient } from '../nodejs/bayar-gg-client.mjs';

const command = process.argv[2] || 'help';
const args = parseArgs(process.argv.slice(3));
const apiKey = process.env.BAYAR_GG_API_KEY || args.apiKey || args.api_key || '';
const baseUrl = process.env.BAYAR_GG_BASE_URL || args.baseUrl || 'https://www.bayar.gg/api';

if (!apiKey && command !== 'help') {
  exitWithHelp('Missing API key. Set BAYAR_GG_API_KEY or pass --api-key=YOUR_API_KEY_HERE.');
}

const client = new BayarGgClient(apiKey, baseUrl);

try {
  const result = await run(command, args);
  if (result !== undefined) printJson(result);
} catch (error) {
  console.error(`Error: ${error.message}`);
  process.exit(1);
}

async function run(cmd, options) {
  switch (cmd) {
    case 'help':
      return printHelp();

    case 'methods':
      return client.getPaymentMethods();

    case 'account-status':
      return client.getAccountStatus();

    case 'statistics':
      return client.getStatistics();

    case 'create-payment':
      return client.createPayment({
        amount: numberOption(options.amount, 10000),
        description: options.description || 'Payment from BAYAR GG CLI',
        customer_name: options.customer_name || options.customerName || '',
        customer_email: options.customer_email || options.customerEmail || '',
        customer_phone: options.customer_phone || options.customerPhone || '',
        payment_method: options.payment_method || options.paymentMethod || 'qris',
        payment_url: options.payment_url || options.paymentUrl || 'https://www.bayar.gg/pay',
        callback_url: options.callback_url || options.callbackUrl || '',
        redirect_url: options.redirect_url || options.redirectUrl || '',
        file_id: options.file_id || options.fileId || '',
        content_id: options.content_id || options.contentId || '',
        product_image_id: options.product_image_id || options.productImageId || '',
        use_qris_converter: booleanOption(options.use_qris_converter || options.useQrisConverter, false),
      });

    case 'check-payment':
      requireOption(options.invoice, 'invoice');
      return client.checkPayment(options.invoice);

    case 'list-payments':
      return client.listPayments({
        search: options.search || '',
        status: options.status || '',
        payment_method: options.payment_method || options.paymentMethod || '',
        paid_via: options.paid_via || options.paidVia || '',
        start_date: options.start_date || options.startDate || '',
        end_date: options.end_date || options.endDate || '',
        page: numberOption(options.page, 1),
        limit: numberOption(options.limit, 10),
      });

    case 'files':
      return client.listFiles(booleanOption(options.active_only || options.activeOnly, true));

    case 'contents':
      return client.listContents(booleanOption(options.active_only || options.activeOnly, true));

    case 'images':
      return client.listImages(booleanOption(options.active_only || options.activeOnly, true));

    case 'qris-convert':
      requireOption(options.qris, 'qris');
      return client.qrisConvert(options.qris, numberOption(options.nominal || options.amount, 10000));

    case 'qris-info':
      requireOption(options.qris, 'qris');
      return client.qrisInfo(options.qris);

    case 'wa-orders':
      return client.waStoreOrders({
        order_number: options.order_number || options.orderNumber || '',
        status: options.status || '',
        search: options.search || '',
        limit: numberOption(options.limit, 50),
        offset: numberOption(options.offset, 0),
      });

    case 'wa-complete':
      requireOption(options.order_number || options.orderNumber, 'order-number');
      return client.completeWaStoreOrder(
        options.order_number || options.orderNumber,
        options.status || 'completed',
        booleanOption(options.notify, true),
      );

    default:
      exitWithHelp(`Unknown command: ${cmd}`);
  }
}

function parseArgs(rawArgs) {
  const parsed = {};
  for (const arg of rawArgs) {
    if (!arg.startsWith('--')) continue;
    const eq = arg.indexOf('=');
    if (eq === -1) {
      parsed[toCamel(arg.slice(2))] = true;
      continue;
    }
    const key = arg.slice(2, eq);
    parsed[toCamel(key)] = arg.slice(eq + 1);
    parsed[key] = arg.slice(eq + 1);
  }
  return parsed;
}

function toCamel(value) {
  return value.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
}

function requireOption(value, name) {
  if (value === undefined || value === null || value === '') {
    throw new Error(`Missing required option --${name}=...`);
  }
}

function numberOption(value, fallback) {
  if (value === undefined || value === null || value === '') return fallback;
  const parsed = Number(value);
  if (!Number.isFinite(parsed)) return fallback;
  return parsed;
}

function booleanOption(value, fallback) {
  if (value === undefined || value === null || value === '') return fallback;
  if (typeof value === 'boolean') return value;
  return ['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase());
}

function printJson(value) {
  console.log(JSON.stringify(value, null, 2));
}

function exitWithHelp(message) {
  if (message) console.error(message);
  printHelp();
  process.exit(message ? 1 : 0);
}

function printHelp() {
  console.log(`BAYAR GG API CLI

Usage:
  BAYAR_GG_API_KEY=YOUR_API_KEY_HERE node examples/cli/bayar-gg-cli.mjs <command> [options]

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
  node examples/cli/bayar-gg-cli.mjs methods
  node examples/cli/bayar-gg-cli.mjs create-payment --amount=10000 --description="Order #1001" --payment-method=qris --payment-url=https://www.bayar.gg/pay
  node examples/cli/bayar-gg-cli.mjs check-payment --invoice=PAY-USERNAME-000001
  node examples/cli/bayar-gg-cli.mjs list-payments --status=paid --limit=10
`);
}
