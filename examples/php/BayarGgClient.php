<?php

final class BayarGgClient
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = 'https://www.bayar.gg/api')
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function createPayment(array $payload): array
    {
        return $this->request('POST', '/create-payment.php', [], $payload);
    }

    public function checkPayment(string $invoiceId): array
    {
        return $this->request('GET', '/check-payment.php', ['invoice_id' => $invoiceId]);
    }

    public function listPayments(array $filters = []): array
    {
        return $this->request('GET', '/list-payments.php', $filters);
    }

    public function getPaymentMethods(): array
    {
        return $this->request('GET', '/get-payment-methods.php');
    }

    public function getAccountStatus(): array
    {
        return $this->request('GET', '/get-account-status.php');
    }

    public function getStatistics(): array
    {
        return $this->request('GET', '/get-statistics.php');
    }

    public function qrisConvert(string $qris, int $amount): array
    {
        return $this->request('POST', '/qris-convert.php', [], [
            'qris' => $qris,
            'amount' => $amount,
        ]);
    }

    public function topupProducts(string $game = 'ml'): array
    {
        return $this->request('GET', '/topup-game/products.php', ['game' => $game]);
    }

    public function createTopupOrder(array $payload): array
    {
        return $this->request('POST', '/topup-game/order.php', [], $payload);
    }

    public function checkTopupStatus(string $orderNumber): array
    {
        return $this->request('GET', '/topup-game/status.php', ['order_number' => $orderNumber]);
    }

    private function request(string $method, string $path, array $query = [], ?array $body = null): array
    {
        $url = $this->baseUrl . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'X-API-Key: ' . $this->apiKey,
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new RuntimeException('BAYAR GG request failed: ' . $error);
        }

        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON response from BAYAR GG. HTTP status: ' . $status);
        }

        if ($status >= 400) {
            $message = $data['error'] ?? $data['message'] ?? 'HTTP error ' . $status;
            throw new RuntimeException($message);
        }

        return $data;
    }
}
