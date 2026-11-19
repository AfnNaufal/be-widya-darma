<?php

namespace App\Http\Requests\Admin;

class TeacherRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'name' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'email' => [$this->requiredOnCreate(), 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable'],
            'image_url' => ['nullable', 'string'],
            'expertise' => ['nullable', 'array'],
            'expertise.*' => ['string', 'max:255'],
            'education' => ['nullable', 'array'],
            'education.*' => ['string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'order' => ['nullable', 'integer'],
        ];
    }
}
