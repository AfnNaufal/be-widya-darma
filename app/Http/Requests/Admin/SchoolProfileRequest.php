<?php

namespace App\Http\Requests\Admin;

use App\Models\SchoolProfile;

class SchoolProfileRequest extends AdminRequest
{
    /**
     * Profil sekolah hanya 1 record: wajib lengkap jika belum ada, opsional jika sudah ada.
     */
    protected function requiredOnCreate(): string
    {
        return SchoolProfile::query()->exists() ? 'sometimes' : 'required';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->adminRules(),
            'school_name' => [$this->requiredOnCreate(), 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'logo_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
