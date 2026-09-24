# Admin Contact Submissions API Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add authenticated admin APIs to list, view, and update the status of contact submissions stored in the backend-owned shared table.

**Architecture:** The admin service maps the existing `contact_submissions` table without adding a migration. A dedicated query action handles safe filtering and sorting, resources separate list summaries from full details, and a transactional use case updates status and `resolved_at` consistently.

**Tech Stack:** PHP 8.2, Laravel 12, Eloquent, Pest 4, Spatie Laravel Permission

**Spec:** User request in the active Codex task dated 2026-09-24.

## Global Constraints

- `glamrush_backend_service` retains migration and public-write ownership of `contact_submissions`.
- The admin service must not create, delete, or migrate contact submissions.
- Allowed statuses are `new`, `in_progress`, `resolved`, and `spam`.
- `resolved_at` is set when status becomes `resolved` and cleared for every other status.
- List sorting is restricted to an explicit allowlist.
- All routes require Sanctum authentication and resource-specific permissions.

---

### Task 1: Contact Submission Admin Resource

**Files:**
- Create: `app/Domain/Contact/Enums/ContactSubmissionStatus.php`
- Create: `app/Models/ContactSubmission.php`
- Create: `app/Domain/Contact/Actions/BuildContactSubmissionQueryAction.php`
- Create: `app/Domain/Contact/UseCases/ListContactSubmissionsUseCase.php`
- Create: `app/Domain/Contact/UseCases/UpdateContactSubmissionStatusUseCase.php`
- Create: `app/Http/Requests/ContactSubmission/ListContactSubmissionsRequest.php`
- Create: `app/Http/Requests/ContactSubmission/UpdateContactSubmissionStatusRequest.php`
- Create: `app/Http/Resources/ContactSubmission/ContactSubmissionListResource.php`
- Create: `app/Http/Resources/ContactSubmission/ContactSubmissionResource.php`
- Create: `app/Http/Controllers/ContactSubmission/ListContactSubmissionsController.php`
- Create: `app/Http/Controllers/ContactSubmission/ShowContactSubmissionController.php`
- Create: `app/Http/Controllers/ContactSubmission/UpdateContactSubmissionStatusController.php`
- Modify: `routes/api.php`
- Modify: `database/seeders/PermissionsSeeder.php`
- Test: `tests/Feature/ContactSubmission/ContactSubmissionManagementTest.php`

**Interfaces:**
- Consumes: the existing shared `contact_submissions`, `categories`, and `customer_accounts` tables.
- Produces: `GET /api/v1/contact-submissions`, `GET /api/v1/contact-submissions/{submission}`, and `PATCH /api/v1/contact-submissions/{submission}/status`.

- [x] **Step 1: Write failing API tests**

Cover authentication, all three permissions, pagination, search/status/source/storefront/customer/date filters, safe sorting, detail fields, status validation, and `resolved_at` synchronization.

- [x] **Step 2: Verify the tests fail before implementation**

Run: `php artisan test tests/Feature/ContactSubmission/ContactSubmissionManagementTest.php`

Expected: route-not-found failures because the admin resource does not exist.

- [x] **Step 3: Implement the model and status enum**

Map the backend-owned table with ULID IDs, guarded attributes, datetime/JSON/status casts, and category/customer relationships. Define enum values `new`, `in_progress`, `resolved`, and `spam`.

- [x] **Step 4: Implement validated querying and resources**

Allow pagination up to 100 rows, text search, exact status/source/storefront/customer filters, inclusive created/resolved date ranges, and allowlisted sorting. Return compact list rows and full message/metadata/customer data from the detail endpoint.

- [x] **Step 5: Implement atomic status updates**

Lock the submission row, update the enum-backed status, set `resolved_at` to the current time only for `resolved`, clear it otherwise, and return the refreshed detailed resource.

- [x] **Step 6: Register routes and permissions**

Protect list with `ViewAny_ContactSubmission`, show with `View_ContactSubmission`, and status update with `Update_ContactSubmission`. Add `ContactSubmission` to the generated permission resource list.

- [x] **Step 7: Run tests and formatting checks**

Run: `php artisan test tests/Feature/ContactSubmission/ContactSubmissionManagementTest.php tests/Feature/AccessControl/AccessControlManagementTest.php`

Run: `php vendor/bin/pint --test` against only files created or modified by this feature.

Expected: all focused tests and formatting checks pass.

- [x] **Step 8: Verify routes and migration ownership**

Run: `php artisan route:list --path=api/v1/contact-submissions`

Run: `rg "contact_submissions" database/migrations`

Expected: three authenticated routes are present and no admin migration owns the shared table.
