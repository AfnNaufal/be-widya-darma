<?php

namespace App\Http\Requests\Admin;

class ProgramRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'title' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
