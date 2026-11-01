<?php

namespace App\Http\Requests\Admin;

use App\Models\News;
use Illuminate\Validation\Rule;

class NewsRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $news = $this->route('news');
        $ignoreId = $news instanceof News ? $news->news_id : null;

        return [
            ...$this->adminRules(),
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('news', 'slug')->ignore($ignoreId, 'news_id')],
            'content' => [$this->requiredOnCreate(), 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
