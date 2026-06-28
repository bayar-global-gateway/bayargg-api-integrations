export class BayarGgClient {
  constructor(apiKey, baseUrl = 'https://www.bayar.gg/api') {
    this.apiKey = apiKey;
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }

  createPayment(payload) {
    return this.request('POST', '/create-payment.php', {}, payload);
  }

  checkPayment(invoiceId) {
    return this.request('GET', '/check-payment.php', { invoice: invoiceId });
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
    return this.request('POST', '/qris-convert.php', {}, { qris, nominal: amount });
  }

  qrisInfo(qris) {
    return this.request('POST', '/qris-info.php', {}, { qris });
  }

  listFiles(activeOnly = true) {
    return this.request('GET', '/list-files.php', { active_only: String(activeOnly) });
  }

  listContents(activeOnly = true) {
    return this.request('GET', '/list-contents.php', { active_only: String(activeOnly) });
  }

  listImages(activeOnly = true) {
    return this.request('GET', '/list-images.php', { active_only: String(activeOnly) });
  }

  waStoreOrders(filters = {}) {
    return this.request('GET', '/wa-store-orders.php', filters);
  }

  completeWaStoreOrder(orderNumber, status = 'completed', notify = true) {
    return this.request('POST', '/wa-store-complete.php', {}, {
      order_number: orderNumber,
      status,
      notify,
    });
  }

  // ── Merchant API (accounts-connect) — butuh paket Premium "Semua Fitur" ──

  merchantStatus() {
    return this.request('GET', '/accounts-connect.php', { action: 'status' });
  }

  merchantInfo(provider) {
    return this.request('GET', '/accounts-connect.php', { provider, action: 'info' });
  }

  merchantBalance(provider) {
    return this.request('GET', '/accounts-connect.php', { provider, action: 'balance' });
  }

  merchantHistory(provider, limit = 20) {
    return this.request('GET', '/accounts-connect.php', { provider, action: 'history', limit });
  }

  // Langkah connect generik (mendukung semua alur: bri/livin/ovo/gopay).
  merchantConnect(payload) {
    return this.request('POST', '/accounts-connect.php', {}, payload);
  }

  merchantSetQris(provider, qrisString) {
    return this.request('POST', '/accounts-connect.php', {}, {
      provider,
      action: 'set_qris',
      qris_string: qrisString,
    });
  }

  merchantDisconnect(provider) {
    return this.request('POST', '/accounts-connect.php', {}, { provider, action: 'disconnect' });
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
