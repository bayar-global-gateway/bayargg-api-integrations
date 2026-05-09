from __future__ import annotations

from typing import Any

import requests


class BayarGgClient:
    def __init__(self, api_key: str, base_url: str = "https://www.bayar.gg/api") -> None:
        self.api_key = api_key
        self.base_url = base_url.rstrip("/")
        self.session = requests.Session()
        self.session.headers.update({
            "Accept": "application/json",
            "X-API-Key": api_key,
        })

    def create_payment(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self._request("POST", "/create-payment.php", json=payload)

    def check_payment(self, invoice_id: str) -> dict[str, Any]:
        return self._request("GET", "/check-payment.php", params={"invoice_id": invoice_id})

    def list_payments(self, **filters: Any) -> dict[str, Any]:
        return self._request("GET", "/list-payments.php", params=filters)

    def get_payment_methods(self) -> dict[str, Any]:
        return self._request("GET", "/get-payment-methods.php")

    def get_account_status(self) -> dict[str, Any]:
        return self._request("GET", "/get-account-status.php")

    def get_statistics(self) -> dict[str, Any]:
        return self._request("GET", "/get-statistics.php")

    def qris_convert(self, qris: str, amount: int) -> dict[str, Any]:
        return self._request("POST", "/qris-convert.php", json={"qris": qris, "amount": amount})

    def topup_products(self, game: str = "ml") -> dict[str, Any]:
        return self._request("GET", "/topup-game/products.php", params={"game": game})

    def create_topup_order(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self._request("POST", "/topup-game/order.php", json=payload)

    def check_topup_status(self, order_number: str) -> dict[str, Any]:
        return self._request("GET", "/topup-game/status.php", params={"order_number": order_number})

    def _request(self, method: str, path: str, **kwargs: Any) -> dict[str, Any]:
        response = self.session.request(method, self.base_url + path, timeout=30, **kwargs)
        try:
            data = response.json()
        except ValueError as exc:
            raise RuntimeError(f"Invalid JSON response. HTTP status: {response.status_code}") from exc

        if response.status_code >= 400:
            message = data.get("error") or data.get("message") or f"HTTP error {response.status_code}"
            raise RuntimeError(message)

        return data
