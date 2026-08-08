<?php

namespace App\Domain\Discount\Services;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DiscountCodeService
{
    public function __construct(private CreateAppLogAction $log) {}

    public function create(array $data, User $actor): DiscountCode
    {
        return DB::transaction(function () use ($data, $actor) {
            $code = DiscountCode::create([...$this->attributes($data), 'created_by_admin_id' => $actor->id, 'updated_by_admin_id' => $actor->id]);
            $this->syncRelations($code, $data);
            $this->audit('DISCOUNT_CODE_CREATED', $code, $actor, array_keys($this->attributes($data)));

            return $code->load(['storefronts', 'targets', 'createdBy', 'updatedBy']);
        });
    }

    public function update(DiscountCode $code, array $data, User $actor): DiscountCode
    {
        if ($this->hasRedemptions($code) && (($data['code'] ?? $code->code) !== $code->code || ($data['type'] ?? $code->type->value) !== $code->type->value)) {
            throw new ConflictHttpException('Code and discount type cannot be changed after the discount has redemptions.');
        }

        return DB::transaction(function () use ($code, $data, $actor) {
            $before = $code->getAttributes();
            $code->update([...$this->attributes($data), 'updated_by_admin_id' => $actor->id]);
            $this->syncRelations($code, $data);
            $changed = array_keys(array_diff_assoc($code->getAttributes(), $before));
            if (array_key_exists('storefront_ids', $data)) {
                $changed[] = 'storefront_ids';
            }
            if (array_key_exists('targets', $data)) {
                $changed[] = 'targets';
            }
            $this->audit('DISCOUNT_CODE_UPDATED', $code, $actor, array_values(array_unique($changed)));

            return $code->load(['storefronts', 'targets', 'createdBy', 'updatedBy']);
        });
    }

    public function setActive(DiscountCode $code, bool $active, User $actor): DiscountCode
    {
        return DB::transaction(function () use ($code, $active, $actor) {
            $code->update(['is_active' => $active, 'updated_by_admin_id' => $actor->id]);
            $this->audit($active ? 'DISCOUNT_CODE_ACTIVATED' : 'DISCOUNT_CODE_DEACTIVATED', $code, $actor, ['is_active']);

            return $code->load(['storefronts', 'targets', 'createdBy', 'updatedBy']);
        });
    }

    public function duplicate(DiscountCode $source, string $newCode, User $actor): DiscountCode
    {
        return DB::transaction(function () use ($source, $newCode, $actor) {
            $copy = $source->replicate(['is_active']);
            $copy->code = $newCode;
            $copy->name = $source->name.' (Copy)';
            $copy->is_active = false;
            $copy->created_by_admin_id = $actor->id;
            $copy->updated_by_admin_id = $actor->id;
            $copy->save();
            $copy->storefronts()->sync($source->storefronts()->pluck('categories.id'));
            foreach ($source->targets as $target) {
                $copy->targets()->create($target->only(['target_type', 'target_id', 'mode']));
            }
            $this->audit('DISCOUNT_CODE_DUPLICATED', $copy, $actor, ['source_discount_code_id', 'code'], ['source_discount_code_id' => $source->id]);

            return $copy->load(['storefronts', 'targets', 'createdBy', 'updatedBy']);
        });
    }

    private function attributes(array $data): array
    {
        return Arr::only($data, ['code', 'name', 'description', 'type', 'value', 'currency', 'maximum_discount_amount', 'minimum_subtotal', 'starts_at', 'ends_at', 'is_active', 'total_usage_limit', 'per_customer_usage_limit', 'first_order_only', 'applies_to_sale_items', 'applies_to_all_storefronts']);
    }

    private function syncRelations(DiscountCode $code, array $data): void
    {
        if (($data['applies_to_all_storefronts'] ?? $code->applies_to_all_storefronts) === true) {
            $code->storefronts()->sync([]);
        } elseif (array_key_exists('storefront_ids', $data)) {
            $code->storefronts()->sync($data['storefront_ids']);
        }
        if (array_key_exists('targets', $data)) {
            $code->targets()->delete();
            foreach ($data['targets'] as $target) {
                $code->targets()->create($target);
            }
        }
    }

    private function hasRedemptions(DiscountCode $code): bool
    {
        return Schema::hasTable('discount_redemptions') && DB::table('discount_redemptions')->where('discount_code_id', $code->id)->exists();
    }

    private function audit(string $event, DiscountCode $code, User $actor, array $changed, array $extra = []): void
    {
        $this->log->run('info', $event, str($event)->lower()->replace('_', ' ')->ucfirst(), [...$extra, 'discount_code_id' => $code->id, 'changed_fields' => $changed], $actor);
    }
}
