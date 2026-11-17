<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendukung login menggunakan 'email' maupun 'username' (sesuai LoginForm.tsx di frontend).
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required_without:username', 'nullable', 'string'],
            'username' => ['required_without:email', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
