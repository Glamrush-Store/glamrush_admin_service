# Split Image Homepage Section Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a configurable homepage divider with two diagonal desktop panels, one mobile image, descriptions, optional CTAs, and deterministic sort ordering.

**Architecture:** Add `split_image_banner` to the shared section enum. Store copy and panel order in the existing JSON `config`; store left, right, and mobile images in single-file media collections on the section. Admin endpoints accept multipart uploads, while both published homepage serializers return safe image URLs and items ordered by `sort_order`.

**Tech Stack:** Laravel 12, PHP enums, Spatie Media Library, Pest.

**Spec:** User request in this task; desktop has two diagonally divided image panels, mobile has one image, desktop panels have required descriptions and optional CTAs.

## Global Constraints

- The admin service owns section writes and media uploads.
- The public backend reads the shared tables and media records; it does not own a migration.
- Existing homepage section types and API payloads remain compatible.
- Published sections and split-banner items are returned in ascending sort order with stable tie-breaking.

---

### Task 1: Admin contract and media

**Files:**
- Modify: `app/Domain/Storefront/Enums/HomepageSectionType.php`
- Modify: `app/Models/StorefrontHomepageSection.php`
- Modify: `app/Http/Requests/Storefront/StorefrontHomepageSectionRequest.php`
- Modify: `app/Http/Resources/Storefront/StorefrontHomepageSectionResource.php`
- Modify: `app/Http/Controllers/Storefront/StorefrontHomepageSectionController.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Storefront/StorefrontMerchandisingTest.php`

**Interfaces:**
- Consumes multipart fields `left_image`, `right_image`, `mobile_image` and `config.items`.
- Produces `split_image_banner` admin resources with `images.left`, `images.right`, and `images.mobile` URLs.

- [x] Add failing create/update and validation tests for the new section.
- [x] Add the enum case and allow only `items` as its config key.
- [x] Validate exactly one `left` and one `right` item, descriptions, integer `sort_order`, paired optional CTA label/URL, and required images.
- [x] Register single-file media collections and the section morph-map alias.
- [x] Attach uploads transactionally and serialize safe image URLs.
- [x] Run the focused admin storefront tests.

### Task 2: Published ordering and public hydration

**Files:**
- Modify: `app/Domain/Storefront/Services/PublishedStorefrontHomepage.php` (admin service)
- Modify: `app/Domain/Storefront/Enums/HomepageSectionType.php` (backend service)
- Modify: `app/Infrastructure/Persistence/Eloquent/Models/StorefrontHomepageSection.php` (backend service)
- Modify: `app/Domain/Storefront/Services/StorefrontHomepageService.php` (backend service)
- Modify: `app/Providers/AppServiceProvider.php` (backend service)
- Modify: `app/Infrastructure/Caching/CacheInvalidationObserver.php` (backend service)
- Test: `tests/Feature/Storefront/StorefrontHomepageTest.php` (backend service)

**Interfaces:**
- Consumes shared section config and media collections.
- Produces two desktop `items`, sorted by `sort_order`, plus a top-level `mobile_image` URL.

- [x] Add a failing public homepage hydration and ordering test.
- [x] Add the backend enum case and media-enabled section model.
- [x] Hydrate left/right items with image, description, optional CTA, and sort order.
- [x] Remove special type-based ordering from the admin internal serializer and order strictly by `display_order`, then `id`.
- [x] Register morph-map and media cache invalidation support.
- [x] Run focused storefront, caching, and regression tests in both services.

