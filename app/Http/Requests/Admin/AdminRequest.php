<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use App\Services\ImageUploadService;
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
     * Jika admin sudah login, admin_id otomatis diambil dari user login.
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

    /**
     * Mengambil data tervalidasi sekaligus mengonversi file gambar / Base64 Data URL menjadi .webp.
     *
     * @return array<string, mixed>
     */
    public function validatedWithWebp(
        string $folder = 'uploads',
        string $urlColumn = 'image_url',
        string $altInputKey = 'image'
    ): array {
        $data = $this->validated();

        $rawImage = $this->file($altInputKey)
            ?? $this->file($urlColumn)
            ?? ($data[$altInputKey] ?? null)
            ?? ($data[$urlColumn] ?? null);

        if ($rawImage !== null && $rawImage !== '') {
            $data[$urlColumn] = app(ImageUploadService::class)->uploadAndConvertToWebp($rawImage, $folder);
        }

        unset($data[$altInputKey]);

        return $data;
    }
}
