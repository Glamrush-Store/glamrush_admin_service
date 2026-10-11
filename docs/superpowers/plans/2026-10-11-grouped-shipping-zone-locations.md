# Grouped Shipping Zone Locations Implementation Plan

**Goal:** Allow one shipping zone and its rates to cover multiple states and cities while preserving existing zones and selecting the most specific matching zone at checkout.

## Tasks

- [x] Add the admin-owned `shipping_zone_locations` migration and backfill legacy singular locations.
- [x] Add admin models, validation, synchronization, and API resource fields for grouped locations.
- [x] Add admin feature coverage for grouped create/update and overlap validation.
- [x] Add public-backend location models and a shared matcher with city > state > country precedence.
- [x] Use the matcher from both shipping quote and checkout repositories, retaining legacy fallback behavior.
- [x] Add public-backend tests for grouped matching precedence.
- [x] Run focused test suites and document the API payload and deployment order.

## API Shape

```json
{
  "name": "Lagos Mainland",
  "country": "NGA",
  "states": ["LA", "OG"],
  "cities": [
    {"state": "LA", "city": "Ikeja"},
    {"state": "LA", "city": "Surulere"}
  ],
  "is_active": true
}
```

When both `states` and `cities` are empty, the zone is treated as a country-wide zone. Existing singular `state` and `city` requests remain accepted during the UI transition.

## Matching Rules

1. Exact city location in the destination state.
2. State location.
3. Country location.
4. Legacy singular columns as a rolling-deployment fallback.

## Deployment Order

1. Deploy and run the admin-service migration.
2. Deploy the public-backend matcher changes.
3. Deploy the admin UI multi-select changes.

