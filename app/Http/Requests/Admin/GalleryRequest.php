<?php

namespace App\Http\Requests\Admin;

class GalleryRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'news_id' => ['nullable', 'integer', 'exists:news,news_id'],
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'image_url' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }
}
