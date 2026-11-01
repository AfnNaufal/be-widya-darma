<?php

namespace App\Http\Requests\Admin;

class KnowledgeBaseRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'category' => [$this->requiredOnCreate(), 'string', 'max:100'],
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'content' => [$this->requiredOnCreate(), 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
