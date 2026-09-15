# Attribute Type CRUD API Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add authenticated administrator CRUD endpoints for the existing `App\Models\AttributeType` records.

**Architecture:** Keep the existing table and model. A dedicated controller handles list, create, show, update, and delete, while FormRequests allowlist and validate query/body input and a resource shapes responses. Existing Spatie permission middleware authorizes each operation; deletion rejects attribute types still referenced by SKU attribute codes.

**Tech Stack:** Laravel 12, Eloquent, Sanctum, Spatie permissions, Pest, Pint.

**Spec:** The request in this task: "make crud api endpoints for AttributeTypeModel"; `database/migrations/2026_02_05_000000_create_attribute_types_table.php` defines the exact schema.

## Global Constraints

- Preserve the dirty `app/Http/Controllers/SkuAttributeCode/SkuAttributeCodeController.php` worktree change.
- Do not change the existing `attribute_types` migration or seed data.
- Use the existing `/api/v1` admin Sanctum and Spatie permission conventions.
- Keep `value` unique and valid as an attribute key; reject deletion while `sku_attribute_codes.type` references it.

---

### Task 1: Route and Authorization Tests

**Files:**
- Create: `tests/Feature/AttributeType/AttributeTypeCrudTest.php`
- Modify: `routes/api.php`
- Modify: `database/seeders/PermissionsSeeder.php`

**Interfaces:**
- Produces five routes under `/api/v1/attribute-types`: GET list, POST create, GET `/{attributeType}`, PUT/PATCH update, DELETE destroy.
- Permission names: `ViewAny_AttributeType`, `View_AttributeType`, `Create_AttributeType`, `Update_AttributeType`, `Delete_AttributeType`.

- [x] **Step 1: Write failing authorization and CRUD tests** using `RefreshDatabase`, `Sanctum::actingAs(User::factory()->create())`, and `Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum'])`; assert unauthenticated 401, unpermitted 403, create 201, show/update/list 200, delete 200, and missing ID 404.
- [x] **Step 2: Run** `php artisan test tests/Feature/AttributeType/AttributeTypeCrudTest.php`; observed missing-route 404 failures.
- [x] **Step 3: Register routes** in the existing authenticated `/api/v1` route group, with the five separate permission names listed in Task 1; add `'AttributeType'` to the generated permission models list.
- [x] **Step 4: Re-run** the targeted Pest file after Task 2; observed all tests passing.

### Task 2: Validation, Resource, and CRUD Behavior

**Files:**
- Create: `app/Http/Controllers/AttributeType/AttributeTypeController.php`
- Create: `app/Http/Requests/AttributeType/ListAttributeTypesRequest.php`
- Create: `app/Http/Requests/AttributeType/UpsertAttributeTypeRequest.php`
- Create: `app/Http/Resources/AttributeType/AttributeTypeResource.php`
- Test: `tests/Feature/AttributeType/AttributeTypeCrudTest.php`

**Interfaces:**
- `ListAttributeTypesRequest::validated()` allows `search`, `category`, `per_page` (1–100), `sort_by` (`id`, `category`, `value`, `label`, `display_type`, `created_at`, `updated_at`), `sort_dir` (`asc`, `desc`).
- `UpsertAttributeTypeRequest::validated()` allows `category` nullable, `value` unique with `/^[a-z][a-z0-9_]*$/`, `label`, and `display_type`; update ignores the bound model's ID.
- `AttributeTypeResource` returns `id`, `category`, `value`, `label`, `display_type`, `created_at`, `updated_at`.

- [x] **Step 1: Add validation/filter tests** asserting duplicate `value` 422, unsafe sort 422, page-size limit 422, search/category filters, and in-use delete 409.
- [x] **Step 2: Run** `php artisan test tests/Feature/AttributeType/AttributeTypeCrudTest.php`; observed missing-route failures before implementation.
- [x] **Step 3: Implement controller** using `ApiResponse::success`, `AttributeTypeResource::collection($query->paginate($filters['per_page'] ?? 15))`, Eloquent route binding, `AttributeType::create($request->validated())`, `$attributeType->update($data)`, and `SkuAttributeCode::where('type', $attributeType->value)->exists()` before deletion.
- [x] **Step 4: Re-run** targeted tests; all passed.

### Task 3: Documentation and Regression Check

**Files:**
- Create: `docs/attribute-type-api.md`
- Test: `tests/Feature/AttributeType/AttributeTypeCrudTest.php`

**Interfaces:**
- Document the five routes, fields, list filters/sort, permissions, and 409 in-use rule.

- [x] **Step 1: Write route and validation documentation** in `docs/attribute-type-api.md` with exact endpoint and permission strings.
- [x] **Step 2: Format** only changed PHP files with `vendor/bin/pint`.
- [x] **Step 3: Run** targeted Pest tests and `php artisan route:list --path=attribute-types`.
- [x] **Step 4: Run** broader relevant tests (seeder and access-control tests) and `git diff --check`; the pre-existing media-seeder assertion failed and is reported separately.

## Self-Review

- The plan covers all CRUD methods, validation, pagination, authorization, not-found behavior, in-use deletion, docs, and tests.
- Interfaces use `AttributeType` route binding, matching the actual Eloquent class and table schema.
- No placeholder steps or schema changes are required.
