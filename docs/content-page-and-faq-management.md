# Content-page and FAQ management

The admin service owns `content_pages`, `content_page_storefronts`, `faq_categories`, `faqs`, and `faq_storefronts`. The customer-facing backend shares the PostgreSQL database and will read only currently published records. No service-to-service HTTP call is used. Public content endpoints, contact submissions, delivery, and analytics remain backend-owned follow-up work.

## Content pages

Supported page types are `about`, `contact`, `privacy_policy`, `terms`, `shipping_policy`, `returns_policy`, and `custom`. Page slugs are lowercase, URL-safe, and globally unique. This deliberately disallows ambiguous global/storefront overrides: each public slug resolves to one configuration, whose storefront associations determine availability.

HTML is sanitized on the server. Allowed elements are paragraphs, line breaks, emphasis, headings `h2`–`h4`, lists, block quotes, and safe links. Scripts, styles, iframes, forms, event attributes, arbitrary attributes, and unsafe URL schemes are removed. Embedded base64 images are rejected; images belong in the existing `content-images` media collection.

Contact pages may contain only validated public `settings`: email, phone, WhatsApp, business hours, address, HTTPS map URL, and allowlisted social links. Credentials, internal escalation data, and message submission are not supported.

## Publication lifecycle

- `draft`: unpublished with no publication time.
- `scheduled`: publication is enabled and `published_at` is in the future.
- `published`: enabled, started, and not expired.
- `unpublished`: disabled after a publication time was assigned.
- `expired`: `expires_at` is in the past, regardless of its enabled flag.

Publishing without a publication time assigns the current application time. A future time is preserved, producing scheduled content. Unpublishing is immediately visible in the shared database. The admin service does not cache draft or published content, so no admin cache invalidation is needed; the customer backend must enforce flags and timestamps authoritatively.

## Storefronts

Global records have `applies_to_all_storefronts: true` and no pivot rows. Storefront-specific records require at least one active, non-deleted root `categories` row. Child, inactive, and deleted categories are rejected. Page/FAQ changes and their storefront associations are committed in one transaction.

## FAQs

Every FAQ belongs to an active FAQ category. Public ordering is deterministic: FAQ category `display_order`, FAQ `display_order`, creation time, then ULID. Categories with FAQ records cannot be deleted until their entries are moved or deleted. Both categories and FAQ entries use soft deletion.

## Administrative API

All paths start with `/api/v1`, require Sanctum authentication, return `401` when unauthenticated, and return `403` without the explicit route permission.

Content pages:

- `GET|POST /content-pages`
- `GET|PATCH|DELETE /content-pages/{contentPage}`
- `POST /content-pages/{contentPage}/publish`
- `POST /content-pages/{contentPage}/unpublish`
- `POST /content-pages/{contentPage}/duplicate` with `{ "slug": "new-unique-slug" }`

FAQ categories:

- `GET|POST /faq-categories`
- `GET|PATCH|DELETE /faq-categories/{faqCategory}`
- `POST /faq-categories/reorder` with `{ "ids": ["ULID", "ULID"] }`

FAQs:

- `GET|POST /faqs`
- `GET|PATCH|DELETE /faqs/{faq}`
- `POST /faqs/{faq}/publish`, `/unpublish`, or `/duplicate`
- `POST /faqs/reorder` with `{ "ids": ["ULID", "ULID"] }`

Page listing accepts `search`, `page_type`, `state`, `storefront_id`, `is_published`, publication/expiration ranges, `sort`, `direction`, and `per_page` up to 100. FAQ listing accepts search, category, state, storefront, publication, safe sorting, and pagination filters.

Permissions follow existing naming conventions: `View_ContentPage`, `Create_ContentPage`, `Update_ContentPage`, `Publish_ContentPage`, `Unpublish_ContentPage`, `Duplicate_ContentPage`, and `Delete_ContentPage`; equivalent explicit permissions exist for `Faq`, plus view/create/update/reorder/delete for `FaqCategory`. Audit records contain actor and record IDs plus changed-field names, never complete HTML or unfiltered request bodies.

## Examples

```json
{
  "slug": "about-us",
  "title": "About Glamrush",
  "navigation_title": "About",
  "content": "<h2>Beauty, thoughtfully curated</h2><p>...</p>",
  "page_type": "about",
  "is_published": true,
  "published_at": "2026-08-10T09:00:00Z",
  "expires_at": null,
  "applies_to_all_storefronts": true,
  "storefront_ids": [],
  "display_order": 10
}
```

```json
{
  "faq_category_id": "FAQ_CATEGORY_ULID",
  "question": "How long does delivery take?",
  "answer": "<p>Delivery depends on your location.</p>",
  "display_order": 10,
  "is_published": true,
  "applies_to_all_storefronts": false,
  "storefront_ids": ["ROOT_STOREFRONT_ULID"]
}
```

Admin publication configures availability but does not bypass customer-backend publication, timestamp, storefront, or cache checks.

## Demo seed data

`ContentManagementSeeder` runs after `AppDataSeeder` from the main `DatabaseSeeder`. It creates ten representative pages, six FAQ categories, and eighteen FAQs. The dataset includes global and storefront-specific content plus draft, scheduled, published, and expired lifecycle examples. It is safe to run repeatedly:

```bash
php artisan db:seed --class=ContentManagementSeeder
```

The standalone command requires the `fragrances` and `skincare` active root storefronts created by `AppDataSeeder`.
