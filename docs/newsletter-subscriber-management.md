# Newsletter subscriber management

The admin service reads the shared `newsletter_subscribers` table directly. The table and its migrations remain owned by `glamrush_backend_service`; this service must not create or alter that schema and does not call the storefront backend over HTTP.

All endpoints require an admin Sanctum bearer token.

## Status meanings

- `pending`: submitted but double opt-in confirmation is incomplete.
- `subscribed`: confirmed and eligible for export.
- `unsubscribed`: opted out and excluded from export.

## List subscribers

`GET /api/v1/newsletter/subscribers`

Permission: `ViewAny_NewsletterSubscriber`

Supported query parameters:

- `page` and `per_page` (1–100)
- `search` (partial email match)
- `status`: `pending`, `subscribed`, or `unsubscribed`
- `source` (exact match)
- `confirmed_from` and `confirmed_to` (ISO-compatible dates/timestamps)
- `created_from` and `created_to` (ISO-compatible dates/timestamps)
- `sort_by`: `email`, `status`, `source`, `confirmed_at`, `unsubscribed_at`, `created_at`, or `updated_at`
- `sort_dir`: `asc` or `desc`

Responses include only the safe administrative representation: ID, email, status, source, consented, confirmed, unsubscribed, created, and updated timestamps. Token hashes, IP hashes, and user-agent data are never returned.

## Show subscriber

`GET /api/v1/newsletter/subscribers/{subscriber}`

Permission: `View_NewsletterSubscriber`

Returns the same safe representation as the list. A missing ULID returns 404.

## Export confirmed subscribers

`GET /api/v1/newsletter/subscribers/export`

Permission: `Export_NewsletterSubscriber`

The endpoint is limited to five requests per minute. It always exports only `subscribed` records and accepts the optional `source`, `confirmed_from`, and `confirmed_to` filters. It streams a UTF-8 CSV ordered by confirmation time and ID.

CSV columns are `email`, `source`, `consented_at`, and `confirmed_at`. Pending and unsubscribed records and all sensitive internal fields are excluded. Values that could trigger spreadsheet formulas are prefixed with an apostrophe.

The admin interface must not create subscribers or change status; public subscription and double-opt-in lifecycle remain owned by the customer-facing backend.
