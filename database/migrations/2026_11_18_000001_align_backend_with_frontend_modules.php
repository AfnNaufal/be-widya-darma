<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel teachers (Direktori Guru sesuai Frontend src/types/teacher.ts)
        Schema::create('teachers', function (Blueprint $table) {
            $table->id('teacher_id');
            $table->foreignId('admin_id')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('role')->default('Tenaga Pendidik');
            $table->string('category', 100)->default('MIPA')->index();
            $table->string('subject')->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('nuptk', 50)->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('image')->nullable();
            $table->json('expertise')->nullable();
            $table->json('education')->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 50)->default('Guru Tetap')->index();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 2. Kolom tambahan pada tabel news agar cocok 100% dengan Galeri Kegiatan & Kategori (termasuk Ekstrakurikuler) di Frontend
        Schema::table('news', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('slug');
            $table->string('category', 100)->default('Berita')->index()->after('author_name');
            $table->string('activity_date', 50)->nullable()->after('category');
            $table->string('status', 50)->default('Terbit')->after('image_url');
            $table->json('crop_config')->nullable()->after('status');
        });

        // 3. Kolom urutan & penanda foto sampul pada tabel gallery
        Schema::table('gallery', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('caption');
            $table->boolean('is_cover')->default(false)->after('sort_order');
        });

        // 4. Kolom tambahan pada tabel spmb agar cocok dengan AdminSpmbSection.tsx di Frontend
        Schema::table('spmb', function (Blueprint $table) {
            $table->string('status_label', 50)->default('Buka')->after('academic_year');
            $table->string('headline')->nullable()->after('status_label');
            $table->text('subheadline')->nullable()->after('headline');
            $table->string('registration_fee', 100)->nullable()->after('subheadline');
            $table->string('contact_phone', 50)->nullable()->after('registration_fee');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('brochure_url')->nullable()->after('contact_email');
            $table->string('brochure_file_name')->nullable()->after('brochure_url');
            $table->string('brochure_uploaded_at', 50)->nullable()->after('brochure_file_name');
            $table->json('waves')->nullable()->after('schedule');
            $table->json('steps')->nullable()->after('waves');
            $table->json('faq')->nullable()->after('steps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'category', 'activity_date', 'status', 'crop_config']);
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_cover']);
        });

        Schema::table('spmb', function (Blueprint $table) {
            $table->dropColumn([
                'status_label',
                'headline',
                'subheadline',
                'registration_fee',
                'contact_phone',
                'contact_email',
                'brochure_url',
                'brochure_file_name',
                'brochure_uploaded_at',
                'waves',
                'steps',
                'faq',
            ]);
        });
    }
};
