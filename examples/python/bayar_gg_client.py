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

    # ── Merchant API (accounts-connect) — butuh paket Premium "Semua Fitur" ──

    def merchant_status(self) -> dict[str, Any]:
        """Status koneksi semua provider (ovo, bri, gopay, livin)."""
        return self._request("GET", "/accounts-connect.php", params={"action": "status"})

    def merchant_info(self, provider: str) -> dict[str, Any]:
        """Info akun/profil merchant. provider: ovo|bri|gopay|livin."""
        return self._request("GET", "/accounts-connect.php", params={"provider": provider, "action": "info"})

    def merchant_balance(self, provider: str) -> dict[str, Any]:
        """Saldo merchant. provider: ovo|gopay|livin (BRI tidak punya saldo)."""
        return self._request("GET", "/accounts-connect.php", params={"provider": provider, "action": "balance"})

    def merchant_history(self, provider: str, limit: int = 20) -> dict[str, Any]:
        """Riwayat transaksi merchant. limit 1-100."""
        return self._request(
            "GET",
            "/accounts-connect.php",
            params={"provider": provider, "action": "history", "limit": limit},
        )

    def merchant_connect(self, payload: dict[str, Any]) -> dict[str, Any]:
        """Langkah connect generik (mendukung semua alur: bri/livin/ovo/gopay)."""
        return self._request("POST", "/accounts-connect.php", json=payload)

    def merchant_set_qris(self, provider: str, qris_string: str) -> dict[str, Any]:
        """Set/hapus string QRIS statis (provider: bri|livin)."""
        return self._request(
            "POST",
            "/accounts-connect.php",
            json={"provider": provider, "action": "set_qris", "qris_string": qris_string},
        )

    def merchant_disconnect(self, provider: str) -> dict[str, Any]:
        """Putuskan koneksi akun merchant."""
        return self._request(
            "POST",
            "/accounts-connect.php",
            json={"provider": provider, "action": "disconnect"},
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
