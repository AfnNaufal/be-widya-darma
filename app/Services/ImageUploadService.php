<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageUploadService
{
    /**
     * Mengunggah gambar (dari UploadedFile atau Base64 Data URL) dan mengompresnya menjadi format .webp.
     * Jika input sudah berupa path/URL biasa (misal: "/images/32.webp"), dikembalikan apa adanya.
     */
    public function uploadAndConvertToWebp(
        UploadedFile|string|null $input,
        string $folder = 'uploads',
        int $maxWidth = 1600,
        int $quality = 90
    ): ?string {
        if ($input === null || $input === '') {
            return null;
        }

        if ($input instanceof UploadedFile) {
            $rawBytes = (string) file_get_contents($input->getRealPath());
        } elseif ($this->isBase64Image($input)) {
            $rawBytes = $this->decodeBase64Image($input);
        } else {
            // Sudah berupa URL atau path statis (misal: /images/32.webp atau https://...)
            return trim($input);
        }

        $webpBytes = $this->convertBytesToWebp($rawBytes, $maxWidth, $quality);

        $cleanFolder = trim($folder, '/');
        $filename = Str::uuid()->toString().'.webp';
        $relativePath = "{$cleanFolder}/{$filename}";

        Storage::disk('public')->put($relativePath, $webpBytes);

        return '/storage/'.$relativePath;
    }

    /**
     * Memeriksa apakah string merupakan Data URL gambar Base64 (seperti dari FileReader.readAsDataURL di frontend).
     */
    public function isBase64Image(string $value): bool
    {
        return (bool) preg_match('/^data:image\/(jpeg|jpg|png|webp|gif);base64,/i', trim($value));
    }

    /**
     * Mendekode Data URL Base64 menjadi binary gambar mentah.
     *
     * @throws InvalidArgumentException
     */
    protected function decodeBase64Image(string $dataUrl): string
    {
        $parts = explode(',', trim($dataUrl), 2);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Format gambar Base64 tidak valid.');
        }

        $decoded = base64_decode($parts[1], true);

        if ($decoded === false || $decoded === '') {
            throw new InvalidArgumentException('Gagal mendekode data gambar Base64.');
        }

        return $decoded;
    }

    /**
     * Mengonversi dan mengompres byte gambar menjadi format WebP menggunakan ekstensi GD.
     */
    protected function convertBytesToWebp(string $rawBytes, int $maxWidth, int $quality): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return $rawBytes;
        }

        $image = @imagecreatefromstring($rawBytes);

        if ($image === false) {
            return $rawBytes;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Resize proporsional jika lebar gambar melebihi batas maksimal
        if ($maxWidth > 0 && $width > $maxWidth) {
            $newHeight = (int) round(($height * $maxWidth) / $width);
            $scaled = imagescale($image, $maxWidth, max(1, $newHeight));
            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        // Pastikan mendukung truecolor dan transparansi alpha untuk PNG -> WebP
        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }
        @imagealphablending($image, true);
        @imagesavealpha($image, true);

        ob_start();
        $success = @imagewebp($image, null, $quality);
        $webpData = (string) ob_get_clean();

        imagedestroy($image);

        if (! $success || $webpData === '') {
            return $rawBytes;
        }

        return $webpData;
    }
}
