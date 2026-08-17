# Glamrush Admin Service

The administrative API and control plane for the Glamrush commerce platform. This Laravel service manages catalog data, merchandising, operational configuration, staff access, and the administrative views of commerce data consumed by the Glamrush Admin frontend.

## Platform context

Glamrush is split across four repositories:

| Repository | Responsibility |
| --- | --- |
| `glamrush_admin_service` | Admin API, catalog ownership, configuration, reporting, and staff authorization |
| `glamrush-admin` | Nuxt administration interface for staff |
| `glamrush_backend_service` | Customer-facing commerce API, authentication, carts, checkout, orders, and payments |
| `glamrush_storefront` | Nuxt customer storefront, currently configured for the fragrances storefront |

The two Laravel services currently use a shared PostgreSQL database. The Admin Service owns catalog schema and catalog writes; the Backend Service reads that catalog and owns customer-commerce workflows. Redis is used for queues, caching, throttling, and cache metrics where configured.

## Core capabilities

- Product, variant, category, brand, vendor, collection, and media management
- Multi-category products with a primary category
- Storefront campaigns, homepage sections, and announcement configuration
- Discount-code definition and catalog/storefront targeting
- Content pages, FAQ categories, and FAQs with publication workflows
- Shipping zones, methods, rates, shipments, and payment-method configuration
- Order, payment transaction, customer, and newsletter administration
- Manual order entry
- Staff users, roles, permissions, and password recovery
- Dashboard analytics and Redis cache monitoring
- Protected internal storefront-merchandising API

See [Feature catalog](docs/features.md) for details.

## Technology

- PHP 8.2+
- Laravel 12
- PostgreSQL
- Laravel Sanctum and Spatie Laravel Permission
- Redis or database-backed cache and queues
- Laravel Octane with RoadRunner/Swoole support
- Spatie Media Library with local or Google Cloud Storage
- Resend-compatible mail delivery
- Pest 4

## Local installation

### Prerequisites

- PHP 8.2 or newer with the extensions required by Laravel and PostgreSQL
- Composer 2
- PostgreSQL
- Redis, recommended for a production-like local environment
- Node.js 20+ and npm, used for Vite assets

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

On PowerShell, replace the copy command with:

```powershell
Copy-Item .env.example .env
```

Configure the database, Redis, mail, filesystem, payment providers, and `STOREFRONT_INTERNAL_API_TOKEN` in `.env`, then initialize the schema:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

When using the shared database, run Admin Service catalog migrations before Backend Service migrations. Do not add Admin-owned catalog migrations to the Backend Service.

### Run locally

```bash
php artisan serve --port=8001
php artisan queue:work
php artisan schedule:work
npm run dev
```

The processes should run in separate terminals. The Admin Nuxt app must point `API_BASE` to this service, for example `http://127.0.0.1:8001/api/v1`.

## Useful commands

```bash
composer test
./vendor/bin/pint
php artisan route:list
php artisan l5-swagger:generate
php artisan optimize:clear
```

Scheduled production tasks include dashboard analytics aggregation and cache-metric aggregation. Run one scheduler for the deployed service and one or more queue workers appropriate to the configured queue backend.

## Configuration groups

| Group | Important variables |
| --- | --- |
| Application | `APP_ENV`, `APP_URL`, `APP_KEY`, `APP_DEBUG` |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Cache and queue | `CACHE_STORE`, `QUEUE_CONNECTION`, `REDIS_*` |
| Internal API | `STOREFRONT_INTERNAL_API_TOKEN`, `STOREFRONT_HOMEPAGE_CACHE_TTL` |
| Mail | `MAIL_MAILER`, `MAIL_FROM_*`, `RESEND_API_KEY` |
| Media | `FILESYSTEM_DISK` and provider-specific storage credentials |
| Payments | `PAYSTACK_*`, `FLUTTERWAVE_*` |
| Runtime | `OCTANE_*`, `ROADRUNNER_RPC_PORT` |

Never commit `.env`, service-account credentials, payment secrets, internal API tokens, or production exports.

## API and authentication

Admin endpoints live under `/api/v1`. Staff requests use Sanctum bearer tokens and permission middleware. The published homepage endpoint under `/api/internal/v1` uses a separate internal-service bearer token and must not be exposed with an empty token.

The Postman collection is available at `GlamRush_Admin_API.postman_collection.json`. Existing endpoint references are indexed in [Feature catalog](docs/features.md).

## Documentation

- [Architecture](docs/architecture.md)
- [Feature catalog](docs/features.md)
- [Cloudflare R2 storage](docs/cloudflare-r2-storage.md)
- [Admin API routes](docs/admin-api-routes.md)
- [Access control](docs/access-control-management.md)
- [Homepage merchandising](docs/storefront-homepage-merchandising.md)
- [Content and FAQ management](docs/content-page-and-faq-management.md)
- [Discount management](docs/discount-code-management.md)
- [Newsletter management](docs/newsletter-subscriber-management.md)
- [Manual order entry](docs/manual-order-entry.md)

## Contribution notes

Keep controllers thin, place use cases and domain rules in the appropriate module, validate input with request objects, enforce permissions on administrative routes, and add Pest coverage for behavior changes. Catalog schema changes belong in this repository.
