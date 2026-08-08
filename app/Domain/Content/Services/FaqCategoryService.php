<?php

namespace App\Domain\Content\Services;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FaqCategoryService
{
    public function __construct(private CreateAppLogAction $log) {}

    public function create(array $data, User $actor): FaqCategory
    {
        return DB::transaction(function () use ($data, $actor) {
            $category = FaqCategory::create($data);
            $this->audit('FAQ_CATEGORY_CREATED', $category, $actor, array_keys($data));

            return $category;
        });
    }

    public function update(FaqCategory $category, array $data, User $actor): FaqCategory
    {
        return DB::transaction(function () use ($category, $data, $actor) {
            $category->update($data);
            $this->audit('FAQ_CATEGORY_UPDATED', $category, $actor, array_keys($data));

            return $category;
        });
    }

    public function delete(FaqCategory $category, User $actor): void
    {
        if ($category->faqs()->exists()) {
            throw new ConflictHttpException('Move or delete this category\'s FAQs before deleting the category.');
        }
        DB::transaction(function () use ($category, $actor) {
            $this->audit('FAQ_CATEGORY_DELETED', $category, $actor, ['deleted_at']);
            $category->delete();
        });
    }

    public function reorder(array $ids, User $actor): void
    {
        DB::transaction(function () use ($ids, $actor) {
            foreach ($ids as $position => $id) {
                FaqCategory::whereKey($id)->update(['display_order' => $position + 1]);
            } $this->log->run('info', 'FAQ_CATEGORY_REORDERED', 'FAQ categories reordered', ['faq_category_ids' => $ids, 'changed_fields' => ['display_order']], $actor);
        });
    }

    private function audit(string $event, FaqCategory $category, User $actor, array $changed): void
    {
        $this->log->run('info', $event, str($event)->lower()->replace('_', ' ')->ucfirst(), ['faq_category_id' => $category->id, 'changed_fields' => $changed], $actor);
    }
}
