<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateAdmin;
use App\Models\Admin;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FrontendIntegrationAndWebpTest extends TestCase
{
    /**
     * Sampel 1x1 piksel PNG transparan dalam format Base64 Data URL (seperti hasil FileReader.readAsDataURL di frontend).
     */
    protected string $sampleBase64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    public function test_image_upload_service_converts_base64_data_url_to_webp(): void
    {
        Storage::fake('public');

        $service = app(ImageUploadService::class);
        $path = $service->uploadAndConvertToWebp($this->sampleBase64Png, 'gallery');

        $this->assertNotNull($path);
        $this->assertStringStartsWith('/storage/gallery/', $path);
        $this->assertStringEndsWith('.webp', $path);

        $relativeDiskPath = str_replace('/storage/', '', $path);
        Storage::disk('public')->assertExists($relativeDiskPath);
    }

    public function test_image_upload_service_converts_uploaded_file_to_webp(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('kegiatan-sekolah.png', 800, 600);
        $service = app(ImageUploadService::class);
        $path = $service->uploadAndConvertToWebp($file, 'news');

        $this->assertNotNull($path);
        $this->assertStringStartsWith('/storage/news/', $path);
        $this->assertStringEndsWith('.webp', $path);

        $relativeDiskPath = str_replace('/storage/', '', $path);
        Storage::disk('public')->assertExists($relativeDiskPath);
    }

    public function test_image_upload_service_preserves_static_frontend_paths(): void
    {
        $service = app(ImageUploadService::class);

        $this->assertSame('/images/32.webp', $service->uploadAndConvertToWebp('/images/32.webp', 'gallery'));
        $this->assertSame('https://widyadarma.sch.id/logo.webp', $service->uploadAndConvertToWebp('https://widyadarma.sch.id/logo.webp', 'profiles'));
    }

    public function test_admin_upload_endpoint_processes_base64_image_to_webp(): void
    {
        Storage::fake('public');

        $admin = new Admin([
            'name' => 'Admin Test',
            'email' => 'admin@widyadarma.sch.id',
        ]);
        $admin->admin_id = 1;

        $this->withoutMiddleware(AuthenticateAdmin::class);
        $this->actingAs($admin);

        $response = $this->postJson('/api/admin/upload', [
            'folder' => 'gallery',
            'image' => $this->sampleBase64Png,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $path = $response->json('data.path');
        $this->assertStringStartsWith('/storage/gallery/', (string) $path);
        $this->assertStringEndsWith('.webp', (string) $path);

        // Verifikasi gambar .webp dapat langsung diakses oleh halaman publik
        $publicImageResponse = $this->get((string) $path);
        $publicImageResponse->assertStatus(200);
    }
}
