<?php

namespace App\Http\Requests\Admin;

class ExtracurricularRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'name' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
