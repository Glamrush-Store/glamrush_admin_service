<?php

namespace App\Domain\ProductImport\Services;

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RemoteProductImageDownloader
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function download(string $url): UploadedFile
    {
        $currentUrl = $url;

        for ($redirect = 0; $redirect <= config('product_imports.image_max_redirects'); $redirect++) {
            $this->assertPublicHttpsUrl($currentUrl);

            $response = Http::withHeaders([
                'Accept' => 'image/jpeg,image/png,image/webp',
                'User-Agent' => config('app.name').' Product Importer',
            ])->connectTimeout(config('product_imports.image_connect_timeout_seconds'))
                ->timeout(config('product_imports.image_timeout_seconds'))
                ->withOptions([
                    'allow_redirects' => false,
                    'stream' => true,
                ])
                ->get($currentUrl);

            if ($response->redirect()) {
                $location = $response->header('Location');
                if (! $location || $redirect === config('product_imports.image_max_redirects')) {
                    throw new InvalidProductImportException("Image URL redirected too many times: {$url}");
                }

                $currentUrl = $this->resolveRedirectUrl($currentUrl, $location);

                continue;
            }

            if (! $response->successful()) {
                throw new InvalidProductImportException(
                    "Image download failed with HTTP {$response->status()}: {$url}"
                );
            }

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            if (! isset(self::MIME_EXTENSIONS[$mime])) {
                throw new InvalidProductImportException("Unsupported image content type '{$mime}': {$url}");
            }

            $body = $this->readLimitedBody($response->toPsrResponse()->getBody());
            if ($body === '') {
                throw new InvalidProductImportException("Image is empty or exceeds the configured size limit: {$url}");
            }

            $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($body);
            if (! isset(self::MIME_EXTENSIONS[$detectedMime])) {
                throw new InvalidProductImportException("Downloaded content is not a supported image: {$url}");
            }

            $extension = self::MIME_EXTENSIONS[$detectedMime];
            $temporaryPath = tempnam(sys_get_temp_dir(), 'product-import-');
            if ($temporaryPath === false || file_put_contents($temporaryPath, $body) === false) {
                throw new RuntimeException('Unable to create a temporary image file.');
            }

            return new UploadedFile(
                $temporaryPath,
                $this->filename($currentUrl, $extension),
                $detectedMime,
                null,
                true
            );
        }

        throw new InvalidProductImportException("Unable to download image: {$url}");
    }

    private function readLimitedBody(\Psr\Http\Message\StreamInterface $stream): string
    {
        $body = '';
        $limit = config('product_imports.max_image_size_bytes');

        while (! $stream->eof()) {
            $body .= $stream->read(8192);

            if (strlen($body) > $limit) {
                throw new InvalidProductImportException('Downloaded image exceeds the configured size limit.');
            }
        }

        return $body;
    }

    private function assertPublicHttpsUrl(string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
            throw new InvalidProductImportException("Image URL must use HTTPS: {$url}");
        }

        if (isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new InvalidProductImportException("Image URL contains unsupported credentials or port: {$url}");
        }

        $host = strtolower((string) $parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            throw new InvalidProductImportException("Image URL cannot target a private host: {$url}");
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : $this->resolveHostAddresses($host);

        if ($addresses === []) {
            throw new InvalidProductImportException("Image host could not be resolved: {$host}");
        }

        foreach ($addresses as $address) {
            if (filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false) {
                throw new InvalidProductImportException("Image URL cannot target a private address: {$url}");
            }
        }
    }

    private function resolveHostAddresses(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if ($records === false) {
            return [];
        }

        return collect($records)
            ->flatMap(fn (array $record) => array_filter([$record['ip'] ?? null, $record['ipv6'] ?? null]))
            ->unique()
            ->values()
            ->all();
    }

    private function resolveRedirectUrl(string $baseUrl, string $location): string
    {
        if (filter_var($location, FILTER_VALIDATE_URL)) {
            return $location;
        }

        $base = parse_url($baseUrl);
        if ($base === false || ! str_starts_with($location, '/')) {
            throw new InvalidProductImportException("Image URL returned an unsupported redirect: {$location}");
        }

        return $base['scheme'].'://'.$base['host']
            .(isset($base['port']) ? ':'.$base['port'] : '')
            .$location;
    }

    private function filename(string $url, string $extension): string
    {
        $basename = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
        $basename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $basename) ?: 'product-image';

        return trim($basename, '-').'.'.$extension;
    }
}
