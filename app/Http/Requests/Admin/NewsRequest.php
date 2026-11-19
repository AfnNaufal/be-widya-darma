<?php

namespace App\Http\Requests\Admin;

use App\Models\News;
use Illuminate\Validation\Rule;

class NewsRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Map camelCase field dari Frontend (AdminGalleryModal.tsx) ke snake_case kolom database
        if ($this->has('authorName') && ! $this->has('author_name')) {
            $this->merge(['author_name' => $this->input('authorName')]);
        }
        if ($this->has('date') && ! $this->has('activity_date')) {
            $this->merge(['activity_date' => $this->input('date')]);
        }
        if ($this->has('cropConfig') && ! $this->has('crop_config')) {
            $this->merge(['crop_config' => $this->input('cropConfig')]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $news = $this->route('news') ?? $this->route('activity');
        $ignoreId = $news instanceof News ? $news->news_id : null;

        return [
            ...$this->adminRules(),
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('news', 'slug')->ignore($ignoreId, 'news_id')],
            'author_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'activity_date' => ['nullable', 'string', 'max:50'],
            'content' => [$this->requiredOnCreate(), 'string'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable'],
            'published_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:Terbit,Draf,Dalam tinjauan,published,draft'],
            'crop_config' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'photos.*.url' => ['required_with:photos', 'string'],
            'photos.*.caption' => ['nullable', 'string', 'max:255'],
            'photos.*.order' => ['nullable', 'integer'],
        ];
    }
}
