<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\Queries;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;

class VendorQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = Vendor::query();
    }

    /**
     * Apply all supported filters
     */
    public function apply(array $filters = []): self
    {
        return $this
            ->active($filters['is_active'] ?? null)
            ->search($filters['search'] ?? null)
            ->country($filters['country'] ?? null)
            ->city($filters['city'] ?? null)
            ->state($filters['state'] ?? null);
    }

    /**
     * Only active / inactive vendors
     */
    public function active(?bool $isActive): self
    {
        if (! is_null($isActive)) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }

    /**
     * Search by name, business name, email
     */
    public function search(?string $term): self
    {
        if ($term) {
            $this->query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('business_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $this;
    }

    /**
     * Filter by country
     */
    public function country(?string $country): self
    {
        if ($country) {
            $this->query->where('country', $country);
        }

        return $this;
    }

    /**
     * Filter by city
     */
    public function city(?string $city): self
    {
        if ($city) {
            $this->query->where('city', $city);
        }

        return $this;
    }

    /**
     * Filter by state
     */
    public function state(?string $state): self
    {
        if ($state) {
            $this->query->where('state', $state);
        }

        return $this;
    }

    /**
     * Default ordering
     */
    public function orderByLatest(): self
    {
        $this->query->latest();

        return $this;
    }

    /**
     * Expose the underlying Builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }
}
