# Manual order entry

Administrators with the `Create_Order` permission can record a completed offline sale through `POST /api/v1/orders/manual`.

The operation creates the order, item snapshots, paid payment and capture transaction, shipment, audit log, and inventory adjustment in one database transaction. No payment gateway is called and discount codes are not evaluated. For stock-managed variants, only stock not reserved by an online checkout can be sold.

Send a unique `Idempotency-Key` header (or `idempotency_key` body field) for every sale. Repeating the same request safely returns the original order; reusing the key with different data returns HTTP 409.

```json
{
  "items": [
    {
      "product_variant_id": "01J...",
      "quantity": 2,
      "unit_price": "12500.00"
    }
  ],
  "currency": "NGN",
  "payment_method_id": "01J...",
  "transaction_reference": "POS-928441",
  "shipping_method_id": "01J...",
  "shipping_zone_id": "01J...",
  "shipping_rate_id": "01J...",
  "shipping_amount": "2500.00",
  "shipping_address": {
    "name": "Ada Okafor",
    "email": "ada@example.com",
    "phone": "+2348000000000",
    "address_line_1": "12 Admiralty Way",
    "city": "Lekki",
    "state": "Lagos",
    "country": "NG"
  },
  "order_status": "completed",
  "shipment_status": "delivered",
  "placed_at": "2026-08-09T10:30:00+01:00"
}
```

`customer_id` is optional. When omitted, the sale is recorded as a guest order using the contact details in `shipping_address`. `shipping_amount` defaults to the selected rate amount, or zero when no rate is selected. Supported order states are `paid`, `processing`, `shipped`, and `completed`; payment is always recorded as paid.

Run `php artisan db:seed --class=PaymentMethodSeeder` to add the offline Cash, POS, and Bank Transfer choices alongside the online gateway methods.
