# Storefront homepage merchandising

This admin service owns hero campaigns and ordered homepage section configuration. A storefront is identified across services by the slug of an active root category (for example, `fragrances`). Product data is not hydrated by the internal endpoint.

## Admin API

All admin endpoints require a Sanctum bearer token and the indicated Spatie permission.

| Method | Endpoint | Permission |
|---|---|---|
| GET, POST | `/api/v1/storefronts/{storefront}/campaigns` | `ViewAny_StorefrontCampaign`, `Create_StorefrontCampaign` |
| GET, PUT, DELETE | `/api/v1/storefronts/{storefront}/campaigns/{campaign}` | `View_StorefrontCampaign`, `Update_StorefrontCampaign`, `Delete_StorefrontCampaign` |
| PATCH | `.../campaigns/{campaign}/enable` or `/disable` | `Update_StorefrontCampaign` |
| GET, POST | `/api/v1/storefronts/{storefront}/homepage-sections` | `ViewAny_StorefrontHomepageSection`, `Create_StorefrontHomepageSection` |
| GET, PUT, DELETE | `/api/v1/storefronts/{storefront}/homepage-sections/{section}` | corresponding View, Update, or Delete permission |
| PATCH | `.../homepage-sections/{section}/enable` or `/disable` | `Update_StorefrontHomepageSection` |
| PUT | `/api/v1/storefronts/{storefront}/homepage-sections/reorder` | `Update_StorefrontHomepageSection` |

Campaign images are multipart fields named `desktop_image` and `mobile_image`. They accept JPEG, PNG, or WebP files up to 10 MB and are stored through Spatie Media Library. Sending a replacement replaces the existing image. A campaign is published when enabled and `starts_at <= now < ends_at`; either boundary may be null. The current campaign is the published campaign with the highest `priority` (most recently updated breaks a tie).

Reordering accepts `{ "section_ids": ["section-ulid", "..."] }`. The listed sections receive consecutive order values beginning at one. IDs must all belong to the requested storefront.

## Section configuration

All limits are optional integers from 1 through 50. Unknown keys are rejected. Supported schemas are:

| Type | Configuration |
|---|---|
| `featured_products` | `limit`; optional `sort` and `direction` |
| `sale_products` | `limit` |
| `category_products` | required existing `category_slug`; optional `limit`, `sort`, `direction` |
| `collection_products` | required existing `collection_slug`; optional `limit` |
| `newest_products` | `limit` |
| `random_categories` | optional `limit`, `require_products` boolean |
| `manual_products` | required `product_ids` array and optional `limit` |

Allowed sort fields are `created_at`, `price`, `sort_order`, and `name`; direction is `asc` or `desc`. Category, collection, and product identifiers are validated against this service's catalog. Manual product IDs are normalized into a pivot table and returned in the administrator's selected order.

## Internal API

`GET /api/internal/v1/storefronts/{storefront}/homepage` requires `Authorization: Bearer <STOREFRONT_INTERNAL_API_TOKEN>`. The token must be a strong shared secret configured independently in both backend services; it is never accepted in a query string. Missing server configuration fails closed. Token comparison is constant-time.

The response contains `{ "data": { "campaign": object|null, "sections": [] } }`. It exposes only the currently scheduled highest-priority active campaign and currently scheduled active sections in `display_order`. It omits priorities, draft flags, administrator IDs, and hydrated products.

Published configuration is cached per storefront for `STOREFRONT_HOMEPAGE_CACHE_TTL` seconds (default 300). Campaign and section creation, update, enable/disable, delete, manual-product sync, and reorder invalidate the storefront key. There is no public invalidation endpoint or unsigned webhook; the customer-facing backend should refresh according to its own cache TTL.

## Environment

```dotenv
STOREFRONT_INTERNAL_API_TOKEN=generate-a-long-random-secret
STOREFRONT_HOMEPAGE_CACHE_TTL=300
```

## Example: fragrances

Create or confirm an active root category with slug `fragrances`. Then POST a campaign to `/api/v1/storefronts/fragrances/campaigns`, and POST sections such as:

```json
{
  "type": "category_products",
  "title": "Perfume oils",
  "config": {"category_slug": "perfume-oils", "limit": 8, "sort": "created_at", "direction": "desc"},
  "display_order": 1,
  "is_active": true
}
```

For a complete local-development dataset with related categories, attribute types and values, products, variants, collections, campaigns, and all supported homepage section types, run:

```shell
php artisan db:seed --class=AppDataSeeder
```

`DatabaseSeeder` calls `AppDataSeeder` automatically during a normal `php artisan db:seed`. The application seeder uses stable slugs and SKUs and can be run repeatedly without duplicating its records.
