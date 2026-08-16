# Admin Service feature catalog

This document describes the business capabilities exposed by the service. The definitive HTTP definitions remain in `routes/api.php`, request validators, resources, OpenAPI annotations, and the Postman collection.

## Administration and access control

- Staff login, logout, identity lookup, and password recovery
- User creation and lifecycle management
- Roles and permission assignment
- Permission middleware on every protected operational capability

See [Access-control management](access-control-management.md).

## Dashboard and observability

- Aggregated sales and operational analytics
- Product sales and stock-alert reporting
- Cache hit/miss metrics by service and cache area
- Redis health visibility and controlled cache flushing
- Scheduled analytics and cache-metric snapshots

## Catalog

- Hierarchical categories and storefront roots
- Products assigned to multiple categories with one primary category
- Simple and variable products with default variants
- Variant pricing, sale pricing, stock, status, SKU, and attribute combinations
- Brands, vendors, collections, media, and SKU attribute codes
- Product-to-collection and product-to-category ordering

Collections are documented in [Collections API](collections-api.md); the broader route index is in [Admin API routes](admin-api-routes.md).

## Storefront merchandising

- Campaign creation, scheduling, activation, and hero content
- Homepage sections for featured, newest, sale, category, collection, and manually selected products
- Section ordering and enable/disable controls
- Storefront announcement text and link configuration
- Protected publication endpoint consumed by the Backend Service

See [Storefront homepage merchandising](storefront-homepage-merchandising.md).

## Content management

- Static content pages with draft, published, and unpublished states
- Page duplication and storefront assignment
- FAQ categories, ordering, search, publication, and duplication
- Seeded content suitable for local development

See [Content-page and FAQ management](content-page-and-faq-management.md).

## Promotions

- Percentage, fixed-value, and shipping-related discount definitions supported by the commerce contract
- Activation windows, usage limits, customer constraints, and minimum requirements
- Storefront, product, variant, category, brand, and collection targeting
- Duplication and activation/deactivation workflows

The Admin Service defines promotions; the Backend Service performs storefront validation and records redemptions during checkout. See [Discount-code management](discount-code-management.md).

## Orders, customers, and payments

- Order listing, detail, status updates, and status history
- Manual order creation for staff-assisted sales
- Customer listing and account context
- Payment-method configuration and transaction visibility
- Paystack and Flutterwave configuration fields

See [Manual order entry](manual-order-entry.md).

## Shipping and fulfillment

- Geographic shipping zones
- Delivery methods and rate rules
- Shipment records and fulfillment status
- Country/state/city location options for operational forms

## Newsletter operations

- Subscriber listing and status filtering
- Subscriber detail
- CSV export of confirmed subscribers for manual import into marketing platforms

Storefront subscription writes are handled by the Backend Service. See [Newsletter subscriber management](newsletter-subscriber-management.md).

## Settings

- Categorized key/value settings
- Storefront announcement and runtime commerce configuration
- Email delivery test endpoint
- Payment, rate-limit, caching, notification, and media-related settings where configured

Settings may contain secrets. Responses and logs must redact sensitive values, and access must remain permission-controlled.

## Media

Spatie Media Library manages media records. The active Laravel filesystem disk determines whether binaries are stored locally or in cloud object storage. Database records and object-storage lifecycle changes should be treated as one operational workflow.
