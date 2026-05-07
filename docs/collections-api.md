# Collections API — Frontend Integration Reference

This document describes every Collections endpoint available in the GlamRush Admin API.
All requests require a Bearer token obtained from `POST /v1/account/login`.

---

## Base URL

```
/v1
```

## Authentication

All endpoints require:
```
Authorization: Bearer <token>
Content-Type: application/json   (or multipart/form-data when uploading an image)
```

---

## Response envelope

Every response is wrapped in a consistent envelope:

```jsonc
// Success (single resource)
{
  "success": true,
  "message": "Success",
  "data": { ... }
}

// Success (paginated list)
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 15,
    "total": 52,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}

// Error
{
  "success": false,
  "message": "Human-readable error",
  "data": null,
  "errors": {
    "field": ["Validation message"]
  }
}
```

---

## Endpoints

### 1. List Collections

```
GET /v1/collections
```

**Query parameters**

| Param      | Type    | Description                              |
|------------|---------|------------------------------------------|
| is_active  | boolean | Filter by active status (`1` or `0`)     |
| search     | string  | Search by name                           |
| sort_by    | string  | Column to sort by (default: `sort_order`)|
| direction  | string  | `asc` or `desc` (default: `asc`)         |
| per_page   | integer | Items per page (default: `15`)           |

**Example request**
```
GET /v1/collections?is_active=1&per_page=10
```

**Example response** `200`
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": "01jpe4k2n8x7vqm3r5t9w0yd6b",
      "name": "Best Sellers",
      "slug": "best-sellers",
      "sort_order": 2,
      "is_active": true,
      "image": {
        "url": "https://cdn.example.com/collections/best-sellers.jpg",
        "thumb": "https://cdn.example.com/collections/best-sellers-thumb.jpg",
        "medium": "https://cdn.example.com/collections/best-sellers-medium.jpg"
      },
      "created_at": "2026-03-15T10:00:00.000000Z",
      "updated_at": "2026-03-15T10:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 5 },
  "links": { "first": "...", "last": "...", "prev": null, "next": null }
}
```

---

### 2. Show Collection

Returns a single collection **with its products**.

```
GET /v1/collections/{id}
```

**Example response** `200`
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": "01jpe4k2n8x7vqm3r5t9w0yd6b",
    "name": "Best Sellers",
    "slug": "best-sellers",
    "description": "Our most-loved products, chosen by the GlamRush community.",
    "sort_order": 2,
    "is_active": true,
    "meta_title": "Best Sellers | GlamRush",
    "meta_description": "Shop the top-rated beauty products loved by thousands of customers.",
    "meta_keywords": null,
    "image": {
      "url": "https://cdn.example.com/...",
      "thumb": "https://cdn.example.com/...",
      "medium": "https://cdn.example.com/..."
    },
    "products": [
      {
        "id": "01jpe5m3p9y8wqn4s6u0x1ze7c",
        "name": "Matte Lipstick - Ruby Red",
        "slug": "matte-lipstick-ruby-red",
        "type": "simple",
        "status": "published",
        "price": 4500.00,
        "sale_price": null,
        "category": null,
        "brand": null,
        "vendor": null,
        "images": [
          {
            "id": 12,
            "name": "lipstick-ruby-red",
            "url": "https://cdn.example.com/...",
            "thumb": "https://cdn.example.com/...",
            "medium": "https://cdn.example.com/..."
          }
        ],
        "created_at": "2026-03-15T10:00:00.000000Z",
        "updated_at": "2026-03-15T10:00:00.000000Z"
      }
    ],
    "created_at": "2026-03-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

> `image` is `null` when no image has been uploaded.
> `products` is an empty array `[]` when no products are attached.

---

### 3. Create Collection

```
POST /v1/collections
Content-Type: multipart/form-data
```

**Body fields**

| Field            | Type    | Required | Notes                          |
|------------------|---------|----------|--------------------------------|
| name             | string  | yes      | Max 255 chars                  |
| is_active        | boolean | yes      | `true` / `false` or `1` / `0` |
| description      | string  | no       |                                |
| sort_order       | integer | no       | Min 0, default `0`             |
| meta_title       | string  | no       | Max 255 chars                  |
| meta_description | string  | no       | Max 255 chars                  |
| meta_keywords    | string  | no       | Max 255 chars                  |
| photo            | file    | no       | jpeg/png/jpg/gif/svg, max 2 MB |

> The slug is auto-generated from `name` — do not send it.

**Example response** `201`
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": "01jpe4k2n8x7vqm3r5t9w0yd6b",
    "name": "Summer Glow",
    "slug": "summer-glow",
    "description": "Radiant picks to keep you glowing all summer long.",
    "sort_order": 3,
    "is_active": true,
    "meta_title": "Summer Glow | GlamRush",
    "meta_description": "...",
    "meta_keywords": null,
    "image": null,
    "products": [],
    "created_at": "2026-03-15T10:00:00.000000Z",
    "updated_at": "2026-03-15T10:00:00.000000Z"
  }
}
```

---

### 4. Update Collection

```
PUT /v1/collections/{id}
Content-Type: multipart/form-data
```

Same fields as Create. All fields are validated on every request — send the full object, not just changed fields.

**Example response** `200`
```json
{
  "success": true,
  "message": "Collection Updated",
  "data": { ... }
}
```

---

### 5. Delete Collection

```
DELETE /v1/collections/{id}
```

Soft-deletes the collection. Attached products are **not** deleted.

**Example response** `200`
```json
{
  "success": true,
  "message": "Collection Deleted",
  "data": null
}
```

---

### 6. Sync Products on a Collection

Replaces the entire product list on the collection. Any products not in the request body are detached.

```
POST /v1/collections/{id}/products
Content-Type: application/json
```

**Body**

```json
{
  "products": [
    { "id": "01jpe5m3p9y8wqn4s6u0x1ze7c", "sort_order": 1 },
    { "id": "01jpe6n4q0z9xro5t7v1y2af8d", "sort_order": 2 },
    { "id": "01jpe7o5r1a0ysp6u8w2z3bg9e", "sort_order": 3 }
  ]
}
```

**Field rules**

| Field                  | Type    | Required | Notes                         |
|------------------------|---------|----------|-------------------------------|
| products               | array   | yes      | Min 1 item                    |
| products[].id          | string  | yes      | Must be an existing product ID|
| products[].sort_order  | integer | no       | Min 0, default `0`            |

> To clear all products from a collection, use this endpoint with a non-empty list then detach individually, or pass a list with only the products you want to keep.

**Example response** `200`
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": "01jpe4k2n8x7vqm3r5t9w0yd6b",
    "name": "Best Sellers",
    "products": [
      { "id": "01jpe5m3p9y8wqn4s6u0x1ze7c", ... },
      { "id": "01jpe6n4q0z9xro5t7v1y2af8d", ... }
    ],
    ...
  }
}
```

---

### 7. Remove a Single Product from a Collection

```
DELETE /v1/collections/{collection_id}/products/{product_id}
```

No request body needed.

**Example response** `200`
```json
{
  "success": true,
  "message": "Product removed from collection",
  "data": null
}
```

---

## Permissions

| Action              | Required permission  |
|---------------------|----------------------|
| List / Show         | `View_Category`      |
| Create              | `Create_Category`    |
| Update              | `Update_Category`    |
| Delete              | `Delete_Category`    |
| Sync / Detach products | `Update_Category` |

---

## Notes for frontend

- **IDs** are ULIDs (26-character strings), not integers.
- **Image upload** — use `multipart/form-data`. When updating, omit `photo` to keep the existing image.
- **Product sync** (`POST /collections/{id}/products`) is a full replace — it detaches any product not included in the request. Use it when managing the full list (e.g. a drag-and-drop product picker). Use the `DELETE /collections/{id}/products/{product_id}` endpoint when removing a single product inline.
- **Pagination** — the list endpoint is paginated. Use `meta.last_page` and `links.next` to implement infinite scroll or page controls.
- **Soft deletes** — deleted collections do not appear in the list endpoint. There is no restore endpoint currently.
