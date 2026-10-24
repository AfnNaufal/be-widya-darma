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
        Schema::create('spmb', function (Blueprint $table) {
            $table->id('spmb_id');
            $table->foreignId('admin_id')->constrained('admins', 'admin_id')->cascadeOnDelete();
            $table->string('academic_year');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('schedule')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb');
    }
};
