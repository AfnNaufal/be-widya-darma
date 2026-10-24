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
        Schema::create('galleries', function (Blueprint $table) {
            $table->id('gallery_id');
            $table->foreignId('admin_id')->constrained('admins', 'admin_id')->cascadeOnDelete();
            $table->foreignId('news_id')->nullable()->constrained('news', 'news_id')->nullOnDelete();
            $table->string('title');
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
