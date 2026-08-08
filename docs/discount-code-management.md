# Discount-code management

The admin service owns only discount configuration: `discount_codes`, `discount_code_storefronts`, and `discount_code_targets`. Both GlamRush services share the PostgreSQL database. The customer-facing backend reads these tables and must own checkout validation, reservations, redemptions, redemption history, and persisted order totals. No service-to-service request is involved, and saving a configuration here never guarantees checkout eligibility.

## Supported behavior

- Types: `percentage`, `fixed_amount`, and `free_shipping`.
- A fixed amount requires a three-letter currency (normally `NGN`). Percentage and free-shipping configurations have no currency. Only percentages may have `maximum_discount_amount`.
- Codes are trimmed, uppercased, unique, at most 64 characters, and contain only letters, digits, `-`, or `_`.
- An enabled code with a future `starts_at` is `scheduled`; it is not eligible before that instant. An enabled current code is `active`; a past `ends_at` is always `expired`. A disabled unscheduled code is `draft`; other disabled codes are `inactive`.
- `total_usage_limit` limits all successful uses. `per_customer_usage_limit` limits uses by one customer and cannot exceed the total. The backend must enforce both atomically.
- `first_order_only` delegates authoritative order-history checking to the backend. `applies_to_sale_items` controls whether already-discounted catalog items may qualify.

## Storefront and catalog targeting

Storefronts are active root `categories` rows. A global code has `applies_to_all_storefronts: true` and no pivot rows. A non-global code requires one or more root category ULIDs; child or inactive categories are rejected.

Targets use only the fixed types `product`, `product_variant`, `category`, `brand`, and `collection`, with `include` or `exclude` mode. No include targets means the whole applicable storefront catalog is potentially eligible. Includes narrow eligibility, while exclusions always win. The backend is responsible for authoritative descendant-category and collection-membership resolution.

## API and permissions

All endpoints are below `/api/v1`, require Sanctum authentication, and return `401` when unauthenticated or `403` without the named permission.

| Endpoint | Permission |
|---|---|
| `GET /discount-codes`, `GET /discount-codes/{id}` | `View_Discount` |
| `POST /discount-codes` | `Create_Discount` |
| `PATCH /discount-codes/{id}` | `Update_Discount` |
| `POST /discount-codes/{id}/activate` | `Activate_Discount` |
| `POST /discount-codes/{id}/deactivate` | `Deactivate_Discount` |
| `POST /discount-codes/{id}/duplicate` | `Duplicate_Discount` |

The list supports `search`, `type`, `state`, `storefront_id`, `is_active`, `starts_at_from`, `starts_at_to`, `ends_at_from`, `ends_at_to`, `sort`, `direction`, and `per_page` (maximum 100). Allowed sort fields are `code`, `name`, `type`, `value`, `starts_at`, `ends_at`, `is_active`, `created_at`, and `updated_at`.

## Example create request

```json
{
  "code": "WELCOME10",
  "name": "New customer welcome discount",
  "description": "Ten percent off a customer's first eligible order.",
  "type": "percentage",
  "value": "10.00",
  "currency": null,
  "maximum_discount_amount": "5000.00",
  "minimum_subtotal": "20000.00",
  "starts_at": null,
  "ends_at": "2026-12-31T23:59:59Z",
  "is_active": true,
  "total_usage_limit": 1000,
  "per_customer_usage_limit": 1,
  "first_order_only": true,
  "applies_to_sale_items": false,
  "applies_to_all_storefronts": false,
  "storefront_ids": ["ROOT_CATEGORY_ULID"],
  "targets": [
    {"target_type": "collection", "target_id": "COLLECTION_ULID", "mode": "include"},
    {"target_type": "product", "target_id": "PRODUCT_ULID", "mode": "exclude"}
  ]
}
```

Success responses use the standard envelope and include safe configuration fields, selected storefronts, targets, creator/updater summaries, timestamps, and derived `state`. Writes synchronize the code and both relationship sets in one database transaction and write a filtered `app_logs` audit event.

The admin service does not cache discount eligibility, usage, or configuration, so no admin-side cache invalidation is required.

## Backend follow-up

The customer backend must add and own `discount_redemptions`, order/order-item discount snapshots, concurrency-safe reservation/release/redemption, customer identity and first-order checks, currency/subtotal checks, sale detection, target traversal, rounding, and totals. Once redemption records exist, code and type become immutable after first usage; activation and deactivation remain available. The admin details endpoint intentionally issues no redemption queries until that table exists.
