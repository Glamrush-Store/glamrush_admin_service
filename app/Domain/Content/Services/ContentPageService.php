<?php

namespace App\Domain\Content\Services;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\ContentPage;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ContentPageService
{
    public function __construct(private HtmlSanitizer $sanitizer, private CreateAppLogAction $log) {}

    public function create(array $data, User $actor): ContentPage
    {
        return DB::transaction(function () use ($data, $actor) {
            $attributes = $this->attributes($data);
            $attributes['content'] = $this->sanitizer->sanitize($attributes['content']);
            $this->ensureContentRemains($attributes['content']);
            $page = ContentPage::create([...$attributes, 'created_by_admin_id' => $actor->id, 'updated_by_admin_id' => $actor->id]);
            $this->syncStorefronts($page, $data);
            $this->audit('CONTENT_PAGE_CREATED', $page, $actor, array_keys($attributes));

            return $this->load($page);
        });
    }

    public function update(ContentPage $page, array $data, User $actor): ContentPage
    {
        return DB::transaction(function () use ($page, $data, $actor) {
            $before = $page->getAttributes();
            $attributes = $this->attributes($data);
            if (isset($attributes['content'])) {
                $attributes['content'] = $this->sanitizer->sanitize($attributes['content']);
                $this->ensureContentRemains($attributes['content']);
            }
            $page->update([...$attributes, 'updated_by_admin_id' => $actor->id]);
            $this->syncStorefronts($page, $data);
            $changed = array_keys(array_diff_assoc($page->getAttributes(), $before));
            if (array_key_exists('storefront_ids', $data)) {
                $changed[] = 'storefront_ids';
            }
            $this->audit('CONTENT_PAGE_UPDATED', $page, $actor, array_values(array_unique($changed)));

            return $this->load($page);
        });
    }

    public function setPublished(ContentPage $page, bool $published, User $actor): ContentPage
    {
        if ($published && $page->expires_at?->isPast()) {
            throw new ConflictHttpException('Expired content cannot be published. Extend or clear its expiration first.');
        }

        return DB::transaction(function () use ($page, $published, $actor) {
            $values = ['is_published' => $published, 'updated_by_admin_id' => $actor->id];
            if ($published && $page->published_at === null) {
                $values['published_at'] = now();
            }
            $page->update($values);
            $this->audit($published ? 'CONTENT_PAGE_PUBLISHED' : 'CONTENT_PAGE_UNPUBLISHED', $page, $actor, ['is_published', ...array_keys(Arr::except($values, ['is_published', 'updated_by_admin_id']))]);

            return $this->load($page);
        });
    }

    public function duplicate(ContentPage $source, string $slug, User $actor): ContentPage
    {
        return DB::transaction(function () use ($source, $slug, $actor) {
            $copy = $source->replicate();
            $copy->slug = $slug;
            $copy->title = $source->title.' (Copy)';
            $copy->is_published = false;
            $copy->published_at = null;
            $copy->expires_at = null;
            $copy->created_by_admin_id = $actor->id;
            $copy->updated_by_admin_id = $actor->id;
            $copy->save();
            $copy->storefronts()->sync($source->storefronts()->pluck('categories.id'));
            $this->audit('CONTENT_PAGE_DUPLICATED', $copy, $actor, ['slug'], ['source_content_page_id' => $source->id]);

            return $this->load($copy);
        });
    }

    public function delete(ContentPage $page, User $actor): void
    {
        DB::transaction(function () use ($page, $actor) {
            $this->audit('CONTENT_PAGE_DELETED', $page, $actor, ['deleted_at']);
            $page->delete();
        });
    }

    private function attributes(array $data): array
    {
        return Arr::only($data, ['slug', 'title', 'navigation_title', 'excerpt', 'content', 'page_type', 'settings', 'meta_title', 'meta_description', 'is_published', 'published_at', 'expires_at', 'applies_to_all_storefronts', 'display_order']);
    }

    private function syncStorefronts(ContentPage $page, array $data): void
    {
        if (($data['applies_to_all_storefronts'] ?? $page->applies_to_all_storefronts) === true) {
            $page->storefronts()->sync([]);
        } elseif (array_key_exists('storefront_ids', $data)) {
            $page->storefronts()->sync($data['storefront_ids']);
        }
    }

    private function load(ContentPage $page): ContentPage
    {
        return $page->load(['storefronts', 'createdBy', 'updatedBy', 'media']);
    }

    private function ensureContentRemains(string $content): void
    {
        if (trim(strip_tags($content)) === '') {
            throw ValidationException::withMessages(['content' => ['Content must contain text after unsafe markup is removed.']]);
        }
    }

    private function audit(string $event, ContentPage $page, User $actor, array $changed, array $extra = []): void
    {
        $this->log->run('info', $event, str($event)->lower()->replace('_', ' ')->ucfirst(), [...$extra, 'content_page_id' => $page->id, 'changed_fields' => $changed], $actor);
    }
}
