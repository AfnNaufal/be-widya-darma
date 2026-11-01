<?php

namespace App\Http\Requests\Admin;

class SpmbRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'academic_year' => [$this->requiredOnCreate(), 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'schedule' => ['nullable', 'string'],
            'start_date' => [$this->requiredOnCreate(), 'date', 'required_with:end_date'],
            'end_date' => [$this->requiredOnCreate(), 'date', 'required_with:start_date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
