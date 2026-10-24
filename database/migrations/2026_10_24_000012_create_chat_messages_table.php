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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->foreignId('session_id')->constrained('chat_sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('kb_id')->nullable()->constrained('knowledge_bases', 'kb_id')->nullOnDelete();
            $table->string('sender', 20); // 'user' or 'bot'
            $table->text('message');
            $table->string('intent')->nullable();
            $table->boolean('is_answered')->default(false);
            $table->integer('tokens_used')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
