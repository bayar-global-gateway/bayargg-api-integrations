#!/usr/bin/env python3

from __future__ import annotations

import argparse
import json
import os
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "python"))

from bayar_gg_client import BayarGgClient


def main() -> int:
    parser = build_parser()
    args = parser.parse_args()

    api_key = args.api_key or os.getenv("BAYAR_GG_API_KEY", "")
    base_url = args.base_url or os.getenv("BAYAR_GG_BASE_URL", "https://www.bayar.gg/api")

    if not api_key:
        parser.error("Missing API key. Set BAYAR_GG_API_KEY or pass --api-key.")

    client = BayarGgClient(api_key, base_url)

    try:
        result = run_command(client, args)
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1

    print(json.dumps(result, indent=2, ensure_ascii=False))
    return 0


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(description="BAYAR GG API Python CLI")
    parser.add_argument("--api-key", default="", help="BAYAR GG API key")
    parser.add_argument("--base-url", default="", help="BAYAR GG API base URL")

    sub = parser.add_subparsers(dest="command", required=True)

    sub.add_parser("methods", help="List payment methods")
    sub.add_parser("account-status", help="Get account status")
    sub.add_parser("statistics", help="Get payment statistics")

    create = sub.add_parser("create-payment", help="Create payment link")
    create.add_argument("--amount", type=int, default=10000)
    create.add_argument("--description", default="Payment from BAYAR GG Python CLI")
    create.add_argument("--customer-name", default="")
    create.add_argument("--customer-email", default="")
    create.add_argument("--customer-phone", default="")
    create.add_argument("--payment-method", default="qris")
    create.add_argument("--callback-url", default="")
    create.add_argument("--redirect-url", default="")
    create.add_argument("--file-id", default="")
    create.add_argument("--content-id", default="")
    create.add_argument("--product-image-id", default="")
    create.add_argument("--use-qris-converter", action="store_true")

    check = sub.add_parser("check-payment", help="Check payment status")
    check.add_argument("--invoice", required=True)

    list_payments = sub.add_parser("list-payments", help="List payments")
    list_payments.add_argument("--search", default="")
    list_payments.add_argument("--status", default="")
    list_payments.add_argument("--payment-method", default="")
    list_payments.add_argument("--paid-via", default="")
    list_payments.add_argument("--start-date", default="")
    list_payments.add_argument("--end-date", default="")
    list_payments.add_argument("--page", type=int, default=1)
    list_payments.add_argument("--limit", type=int, default=10)

    for name in ("files", "contents", "images"):
        command = sub.add_parser(name, help=f"List {name}")
        command.add_argument("--include-inactive", action="store_true")

    qris_convert = sub.add_parser("qris-convert", help="Convert static QRIS")
    qris_convert.add_argument("--qris", required=True)
    qris_convert.add_argument("--nominal", type=int, default=10000)

    qris_info = sub.add_parser("qris-info", help="Decode QRIS info")
    qris_info.add_argument("--qris", required=True)

    wa_orders = sub.add_parser("wa-orders", help="List WhatsApp Store orders")
    wa_orders.add_argument("--order-number", default="")
    wa_orders.add_argument("--status", default="")
    wa_orders.add_argument("--search", default="")
    wa_orders.add_argument("--limit", type=int, default=50)
    wa_orders.add_argument("--offset", type=int, default=0)

    wa_complete = sub.add_parser("wa-complete", help="Complete WhatsApp Store order")
    wa_complete.add_argument("--order-number", required=True)
    wa_complete.add_argument("--status", default="completed")
    wa_complete.add_argument("--no-notify", action="store_true")

    return parser


def run_command(client: BayarGgClient, args: argparse.Namespace) -> dict:
    if args.command == "methods":
        return client.get_payment_methods()
    if args.command == "account-status":
        return client.get_account_status()
    if args.command == "statistics":
        return client.get_statistics()
    if args.command == "create-payment":
        return client.create_payment({
            "amount": args.amount,
            "description": args.description,
            "customer_name": args.customer_name,
            "customer_email": args.customer_email,
            "customer_phone": args.customer_phone,
            "payment_method": args.payment_method,
            "callback_url": args.callback_url,
            "redirect_url": args.redirect_url,
            "file_id": args.file_id,
            "content_id": args.content_id,
            "product_image_id": args.product_image_id,
            "use_qris_converter": args.use_qris_converter,
        })
    if args.command == "check-payment":
        return client.check_payment(args.invoice)
    if args.command == "list-payments":
        return client.list_payments(
            search=args.search,
            status=args.status,
            payment_method=args.payment_method,
            paid_via=args.paid_via,
            start_date=args.start_date,
            end_date=args.end_date,
            page=args.page,
            limit=args.limit,
        )
    if args.command == "files":
        return client.list_files(active_only=not args.include_inactive)
    if args.command == "contents":
        return client.list_contents(active_only=not args.include_inactive)
    if args.command == "images":
        return client.list_images(active_only=not args.include_inactive)
    if args.command == "qris-convert":
        return client.qris_convert(args.qris, args.nominal)
    if args.command == "qris-info":
        return client.qris_info(args.qris)
    if args.command == "wa-orders":
        return client.wa_store_orders(
            order_number=args.order_number,
            status=args.status,
            search=args.search,
            limit=args.limit,
            offset=args.offset,
        )
    if args.command == "wa-complete":
        return client.complete_wa_store_order(args.order_number, args.status, notify=not args.no_notify)

    raise ValueError(f"Unknown command: {args.command}")


if __name__ == "__main__":
    raise SystemExit(main())
