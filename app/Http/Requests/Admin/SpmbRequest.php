<?php

namespace App\Http\Requests\Admin;

class SpmbRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Map camelCase field dari Frontend (AdminSpmbSection.tsx) ke snake_case kolom database
        $camelMap = [
            'academicYear' => 'academic_year',
            'status' => 'status_label',
            'registrationFee' => 'registration_fee',
            'contactPhone' => 'contact_phone',
            'contactEmail' => 'contact_email',
            'brochureUrl' => 'brochure_url',
            'brochureFileName' => 'brochure_file_name',
            'brochureUploadedAt' => 'brochure_uploaded_at',
        ];

        foreach ($camelMap as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $this->merge([$snake => $this->input($camel)]);
            }
        }

        if (is_array($this->input('requirements'))) {
            $this->merge(['requirements' => implode("\n", $this->input('requirements'))]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'academic_year' => [$this->requiredOnCreate(), 'string', 'max:20'],
            'status_label' => ['nullable', 'string', 'max:50'],
            'headline' => ['nullable', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string'],
            'registration_fee' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'brochure_url' => ['nullable', 'string'],
            'brochure' => ['nullable'],
            'brochure_file_name' => ['nullable', 'string', 'max:255'],
            'brochure_uploaded_at' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'schedule' => ['nullable', 'string'],
            'waves' => ['nullable', 'array'],
            'steps' => ['nullable', 'array'],
            'faq' => ['nullable', 'array'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
