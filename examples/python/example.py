import json
import os

from bayar_gg_client import BayarGgClient


def main() -> None:
    api_key = os.getenv("BAYAR_GG_API_KEY", "YOUR_API_KEY_HERE")
    client = BayarGgClient(api_key)

    methods = client.get_payment_methods()
    print("Payment methods:")
    print(json.dumps(methods, indent=2))

    payment = client.create_payment({
        "amount": 10000,
        "description": "Test payment from Python",
        "customer_name": "BAYAR GG Customer",
        "customer_email": "customer@example.com",
        "customer_phone": "6281234567890",
        "payment_method": "qris",
        "payment_url": "https://www.bayar.gg/pay",
        "callback_url": "https://example.com/webhook/bayar-gg",
        "redirect_url": "https://example.com/thank-you",
    })
    print("Created payment:")
    print(json.dumps(payment, indent=2))


if __name__ == "__main__":
    main()
