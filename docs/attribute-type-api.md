# Attribute type administration API

The admin service manages the existing `attribute_types` table through authenticated `/api/v1` endpoints. Its model is `App\Models\AttributeType` (not `AttributeTypeModel`). All routes use the admin Sanctum guard and a distinct Spatie permission.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/api/v1/attribute-types` | `ViewAny_AttributeType` | Paginated list |
| POST | `/api/v1/attribute-types` | `Create_AttributeType` | Create |
| GET | `/api/v1/attribute-types/{attributeType}` | `View_AttributeType` | Details |
| PUT/PATCH | `/api/v1/attribute-types/{attributeType}` | `Update_AttributeType` | Update |
| DELETE | `/api/v1/attribute-types/{attributeType}` | `Delete_AttributeType` | Delete |

`{attributeType}` is the numeric ID. Responses use the standard `success`, `message`, and `data` envelope; lists also include `meta` and `links`. Each record contains `id`, nullable `category`, unique `value`, `label`, `display_type`, `created_at`, and `updated_at`.

Create body example:

```json
{
  "category": "Fragrance",
  "value": "volume",
  "label": "Volume",
  "display_type": "select"
}
```

`value`, `label`, and `display_type` are required on create. PUT and PATCH accept partial changes. `value` must begin with a lowercase letter and contain only lowercase letters, digits, and underscores; it must be unique. `category` may be omitted or set to `null`. Validation failures return 422.

List query parameters: `page` (positive integer), `per_page` (1–100, default 15), `search` (matches value or label), `category` (exact match), `sort_by` (`id`, `category`, `value`, `label`, `display_type`, `created_at`, `updated_at`), and `sort_dir` (`asc` or `desc`). Default order is `value` ascending, then ID.

Deletion or renaming of a type whose `value` is referenced by `sku_attribute_codes.type` returns 409, preserving existing SKU attribute-code relationships. Label, category, and display-type updates remain possible for an in-use type. Missing records return 404; unauthenticated and unauthorized requests return 401 and 403 respectively.
