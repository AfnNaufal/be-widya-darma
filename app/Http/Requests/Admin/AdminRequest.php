<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;

abstract class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Jika admin sudah login (setelah auth dibuat), admin_id otomatis diambil dari user login.
     * Sebelum auth tersedia, admin_id wajib dikirim manual di body request.
     */
    protected function prepareForValidation(): void
    {
        $admin = $this->user();

        if ($admin instanceof Admin) {
            $this->merge(['admin_id' => $admin->admin_id]);
        }
    }

    /**
     * Field wajib saat create (POST), opsional saat update (PUT/PATCH).
     */
    protected function requiredOnCreate(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function adminRules(): array
    {
        return [
            'admin_id' => [$this->requiredOnCreate(), 'integer', 'exists:admins,admin_id'],
        ];
    }
}
