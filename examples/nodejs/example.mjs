import { BayarGgClient } from './bayar-gg-client.mjs';

const apiKey = process.env.BAYAR_GG_API_KEY || 'YOUR_API_KEY_HERE';
const client = new BayarGgClient(apiKey);

try {
  const methods = await client.getPaymentMethods();
  console.log('Payment methods:');
  console.log(JSON.stringify(methods, null, 2));

  const payment = await client.createPayment({
    amount: 10000,
    description: 'Test payment from Node.js',
    customer_name: 'BAYAR GG Customer',
    customer_email: 'customer@example.com',
    customer_phone: '6281234567890',
    payment_method: 'qris',
    callback_url: 'https://example.com/webhook/bayar-gg',
    redirect_url: 'https://example.com/thank-you',
  });

  console.log('Created payment:');
  console.log(JSON.stringify(payment, null, 2));
} catch (error) {
  console.error('Error:', error.message);
  process.exit(1);
}
