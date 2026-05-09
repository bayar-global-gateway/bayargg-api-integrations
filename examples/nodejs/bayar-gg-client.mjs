export class BayarGgClient {
  constructor(apiKey, baseUrl = 'https://www.bayar.gg/api') {
    this.apiKey = apiKey;
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }

  createPayment(payload) {
    return this.request('POST', '/create-payment.php', {}, payload);
  }

  checkPayment(invoiceId) {
    return this.request('GET', '/check-payment.php', { invoice_id: invoiceId });
  }

  listPayments(filters = {}) {
    return this.request('GET', '/list-payments.php', filters);
  }

  getPaymentMethods() {
    return this.request('GET', '/get-payment-methods.php');
  }

  getAccountStatus() {
    return this.request('GET', '/get-account-status.php');
  }

  getStatistics() {
    return this.request('GET', '/get-statistics.php');
  }

  qrisConvert(qris, amount) {
    return this.request('POST', '/qris-convert.php', {}, { qris, amount });
  }

  topupProducts(game = 'ml') {
    return this.request('GET', '/topup-game/products.php', { game });
  }

  createTopupOrder(payload) {
    return this.request('POST', '/topup-game/order.php', {}, payload);
  }

  checkTopupStatus(orderNumber) {
    return this.request('GET', '/topup-game/status.php', { order_number: orderNumber });
  }

  async request(method, path, query = {}, body = null) {
    const url = new URL(this.baseUrl + path);
    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(key, String(value));
      }
    }

    const headers = {
      Accept: 'application/json',
      'X-API-Key': this.apiKey,
    };

    const options = { method, headers };
    if (body !== null) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const text = await response.text();
    let data;

    try {
      data = JSON.parse(text);
    } catch (error) {
      throw new Error(`Invalid JSON response. HTTP status: ${response.status}`);
    }

    if (!response.ok) {
      throw new Error(data.error || data.message || `HTTP error ${response.status}`);
    }

    return data;
  }
}
