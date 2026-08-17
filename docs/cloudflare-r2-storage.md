# Cloudflare R2 storage

The Admin Service writes catalog and merchandising media. Cloudflare R2 is configured through Laravel's S3-compatible filesystem driver and Spatie Media Library.

## Cloudflare setup

1. Create one R2 bucket for the environment, such as `commerce-media-production`.
2. Create an R2 API token with **Object Read & Write** access scoped only to that bucket.
3. Copy the generated Access Key ID, Secret Access Key, and S3 endpoint. The endpoint has the form `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`; jurisdictional buckets may use a different endpoint.
4. Connect a production custom domain such as `media.example.com` to the bucket.
5. Make the custom domain publicly readable because current product resources return permanent media URLs. The `r2.dev` address is intended only for development.
6. Configure Cloudflare cache and security rules on the custom domain as appropriate.

Uploads pass through this Laravel service, so bucket CORS is not required for the current upload flow. Add a restrictive CORS policy only if direct browser uploads are introduced later.

## Application environment

```dotenv
FILESYSTEM_DISK=r2
MEDIA_DISK=r2

R2_ACCESS_KEY_ID=your_bucket_scoped_access_key
R2_SECRET_ACCESS_KEY=your_bucket_scoped_secret
R2_BUCKET=commerce-media-production
R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
R2_URL=https://media.example.com
R2_REGION=auto
R2_USE_PATH_STYLE_ENDPOINT=false
```

`R2_ENDPOINT` is the authenticated S3 API endpoint. `R2_URL` is the public custom domain used in API responses; do not set it to the dashboard URL. Do not append the bucket name to the account endpoint.

After changing deployment secrets:

```bash
php artisan optimize:clear
php artisan queue:restart
php artisan octane:reload
```

Run only the process commands used by the deployment. A fresh container/release does not need to reload an old process.

## Connection test

Use Tinker in the target environment:

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('r2')->put('health/r2.txt', 'R2 connection OK');
Storage::disk('r2')->exists('health/r2.txt');
Storage::disk('r2')->url('health/r2.txt');
Storage::disk('r2')->delete('health/r2.txt');
```

Confirm that the returned URL starts with `R2_URL` and is readable over HTTPS.

## Existing Google Cloud media

Changing `MEDIA_DISK` affects new uploads only. Existing Spatie `media` rows retain their original `disk` and `conversions_disk` values and continue resolving through the `gcs` disk.

To complete a GCS-to-R2 cutover:

1. Back up the database and source bucket.
2. Copy every object to R2 while preserving its exact object key.
3. Compare object counts and sample checksums.
4. In a maintenance window, change the relevant `media.disk` and `media.conversions_disk` values from `gcs` to `r2`.
5. Verify original, thumbnail, medium, and large URLs from both Admin and Storefront APIs.
6. Keep GCS readable until logs and production traffic confirm the cutover.

Do not update the database before objects and conversions exist at identical R2 keys.

## Permissions and security

- Store R2 credentials in deployment secrets, never Git or Admin UI browser configuration.
- Scope the Admin token to one bucket with Object Read & Write.
- Use separate tokens per environment.
- Rotate a token immediately if it is exposed.
- Keep the S3 API endpoint private; only the media custom domain should be customer-facing.
- Public media must not contain private customer documents or exports.

Cloudflare references: [S3-compatible API](https://developers.cloudflare.com/r2/get-started/s3/) and [public buckets/custom domains](https://developers.cloudflare.com/r2/buckets/public-buckets/).
