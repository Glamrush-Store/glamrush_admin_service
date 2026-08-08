<?php

namespace App\Domain\Content\Services;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FaqService
{
    public function __construct(private HtmlSanitizer $sanitizer, private CreateAppLogAction $log) {}

    public function create(array $data, User $actor): Faq
    {
        return DB::transaction(function () use ($data, $actor) {
            $attributes = $this->attributes($data);
            $attributes['answer'] = $this->sanitizer->sanitize($attributes['answer']);
            $this->ensureAnswerRemains($attributes['answer']);
            $faq = Faq::create([...$attributes, 'created_by_admin_id' => $actor->id, 'updated_by_admin_id' => $actor->id]);
            $this->syncStorefronts($faq, $data);
            $this->audit('FAQ_CREATED', $faq, $actor, array_keys($attributes));

            return $this->load($faq);
        });
    }

    public function update(Faq $faq, array $data, User $actor): Faq
    {
        return DB::transaction(function () use ($faq, $data, $actor) {
            $before = $faq->getAttributes();
            $attributes = $this->attributes($data);
            if (isset($attributes['answer'])) {
                $attributes['answer'] = $this->sanitizer->sanitize($attributes['answer']);
                $this->ensureAnswerRemains($attributes['answer']);
            }
            $faq->update([...$attributes, 'updated_by_admin_id' => $actor->id]);
            $this->syncStorefronts($faq, $data);
            $changed = array_keys(array_diff_assoc($faq->getAttributes(), $before));
            if (array_key_exists('storefront_ids', $data)) {
                $changed[] = 'storefront_ids';
            }
            $this->audit('FAQ_UPDATED', $faq, $actor, array_values(array_unique($changed)));

            return $this->load($faq);
        });
    }

    public function setPublished(Faq $faq, bool $published, User $actor): Faq
    {
        if ($published && $faq->expires_at?->isPast()) {
            throw new ConflictHttpException('Expired FAQ content cannot be published. Extend or clear its expiration first.');
        }

        return DB::transaction(function () use ($faq, $published, $actor) {
            $values = ['is_published' => $published, 'updated_by_admin_id' => $actor->id];
            if ($published && $faq->published_at === null) {
                $values['published_at'] = now();
            }
            $faq->update($values);
            $this->audit($published ? 'FAQ_PUBLISHED' : 'FAQ_UNPUBLISHED', $faq, $actor, ['is_published']);

            return $this->load($faq);
        });
    }

    public function duplicate(Faq $source, User $actor): Faq
    {
        return DB::transaction(function () use ($source, $actor) {
            $copy = $source->replicate();
            $copy->question = $source->question.' (Copy)';
            $copy->is_published = false;
            $copy->published_at = null;
            $copy->expires_at = null;
            $copy->created_by_admin_id = $actor->id;
            $copy->updated_by_admin_id = $actor->id;
            $copy->save();
            $copy->storefronts()->sync($source->storefronts()->pluck('categories.id'));
            $this->audit('FAQ_DUPLICATED', $copy, $actor, ['question'], ['source_faq_id' => $source->id]);

            return $this->load($copy);
        });
    }

    public function delete(Faq $faq, User $actor): void
    {
        DB::transaction(function () use ($faq, $actor) {
            $this->audit('FAQ_DELETED', $faq, $actor, ['deleted_at']);
            $faq->delete();
        });
    }

    public function reorder(array $ids, User $actor): void
    {
        DB::transaction(function () use ($ids, $actor) {
            foreach ($ids as $position => $id) {
                Faq::whereKey($id)->update(['display_order' => $position + 1, 'updated_by_admin_id' => $actor->id]);
            } $this->log->run('info', 'FAQ_REORDERED', 'FAQs reordered', ['faq_ids' => $ids, 'changed_fields' => ['display_order']], $actor);
        });
    }

    private function attributes(array $data): array
    {
        return Arr::only($data, ['faq_category_id', 'question', 'answer', 'display_order', 'is_published', 'published_at', 'expires_at', 'applies_to_all_storefronts']);
    }

    private function syncStorefronts(Faq $faq, array $data): void
    {
        if (($data['applies_to_all_storefronts'] ?? $faq->applies_to_all_storefronts) === true) {
            $faq->storefronts()->sync([]);
        } elseif (array_key_exists('storefront_ids', $data)) {
            $faq->storefronts()->sync($data['storefront_ids']);
        }
    }

    private function load(Faq $faq): Faq
    {
        return $faq->load(['category', 'storefronts', 'createdBy', 'updatedBy']);
    }

    private function ensureAnswerRemains(string $answer): void
    {
        if (trim(strip_tags($answer)) === '') {
            throw ValidationException::withMessages(['answer' => ['Answer must contain text after unsafe markup is removed.']]);
        }
    }

    private function audit(string $event, Faq $faq, User $actor, array $changed, array $extra = []): void
    {
        $this->log->run('info', $event, str($event)->lower()->replace('_', ' ')->ucfirst(), [...$extra, 'faq_id' => $faq->id, 'changed_fields' => $changed], $actor);
    }
}
