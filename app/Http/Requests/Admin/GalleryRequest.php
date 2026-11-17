<?php

namespace App\Http\Requests\Admin;

class GalleryRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $imageRule = $this->isMethod('post') ? 'required_without:image' : 'sometimes';

        return [
            ...$this->adminRules(),
            'news_id' => ['nullable', 'integer', 'exists:news,news_id'],
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'image_url' => [$imageRule, 'nullable', 'string'],
            'image' => ['nullable'],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }
}
