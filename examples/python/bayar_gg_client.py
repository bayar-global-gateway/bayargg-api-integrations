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
        return self._request("GET", "/check-payment.php", params={"invoice": invoice_id})

    def list_payments(self, **filters: Any) -> dict[str, Any]:
        return self._request("GET", "/list-payments.php", params=filters)

    def get_payment_methods(self) -> dict[str, Any]:
        return self._request("GET", "/get-payment-methods.php")

    def get_account_status(self) -> dict[str, Any]:
        return self._request("GET", "/get-account-status.php")

    def get_statistics(self) -> dict[str, Any]:
        return self._request("GET", "/get-statistics.php")

    def qris_convert(self, qris: str, amount: int) -> dict[str, Any]:
        return self._request("POST", "/qris-convert.php", json={"qris": qris, "nominal": amount})

    def qris_info(self, qris: str) -> dict[str, Any]:
        return self._request("POST", "/qris-info.php", json={"qris": qris})

    def list_files(self, active_only: bool = True) -> dict[str, Any]:
        return self._request("GET", "/list-files.php", params={"active_only": str(active_only).lower()})

    def list_contents(self, active_only: bool = True) -> dict[str, Any]:
        return self._request("GET", "/list-contents.php", params={"active_only": str(active_only).lower()})

    def list_images(self, active_only: bool = True) -> dict[str, Any]:
        return self._request("GET", "/list-images.php", params={"active_only": str(active_only).lower()})

    def wa_store_orders(self, **filters: Any) -> dict[str, Any]:
        return self._request("GET", "/wa-store-orders.php", params=filters)

    def complete_wa_store_order(
        self,
        order_number: str,
        status: str = "completed",
        notify: bool = True,
    ) -> dict[str, Any]:
        return self._request(
            "POST",
            "/wa-store-complete.php",
            json={"order_number": order_number, "status": status, "notify": notify},
        )

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
