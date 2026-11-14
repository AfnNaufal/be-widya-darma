<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Services\ChatbotService;
use App\Services\GeminiService;
use Mockery\MockInterface;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    protected string $visitorToken = 'test-visitor-token-12345';

    protected function setUp(): void
    {
        parent::setUp();

        // Bersihkan session test jika ada
        ChatSession::where('visitor_token', $this->visitorToken)->delete();
    }

    public function test_fails_when_input_is_invalid(): void
    {
        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => '',
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['visitor_token', 'message']);
    }

    public function test_greeting_is_answered_directly_without_llm(): void
    {
        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => $this->visitorToken,
            'message' => 'Halo selamat pagi',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'visitor_token' => $this->visitorToken,
                    'topic' => 'greeting',
                    'is_answered' => true,
                    'tokens_used' => 0,
                    'kb_id' => null,
                ],
            ]);

        $this->assertDatabaseHas('chat_sessions', [
            'visitor_token' => $this->visitorToken,
        ]);
    }

    public function test_out_of_scope_question_is_rejected_politely_by_rule_base(): void
    {
        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => $this->visitorToken,
            'message' => 'Bagaimana resep cara membuat rendang sapi padang yang empuk?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'visitor_token' => $this->visitorToken,
                    'topic' => 'out_of_scope',
                    'is_answered' => false,
                    'tokens_used' => 0,
                    'kb_id' => null,
                ],
            ]);
    }

    public function test_school_question_without_kb_returns_polite_fallback_without_llm(): void
    {
        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => $this->visitorToken,
            'message' => 'Apakah di SMA Widya Darma ada laboratorium astronomi roket antariksa?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'visitor_token' => $this->visitorToken,
                    'topic' => 'unknown',
                    'is_answered' => false,
                    'tokens_used' => 0,
                    'kb_id' => null,
                ],
            ]);
    }

    public function test_school_question_with_kb_calls_gemini_service(): void
    {
        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateAnswer')
                ->once()
                ->andReturn([
                    'success' => true,
                    'text' => 'Di SMA Widya Darma tersedia berbagai macam ekstrakurikuler seperti Pramuka, Paskibra, Futsal, Basket, dan Rohis.',
                    'tokens_used' => 85,
                    'error' => null,
                ]);
        });

        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => $this->visitorToken,
            'message' => 'Apa saja ekstrakurikuler yang ada di SMA Widya Darma?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'visitor_token' => $this->visitorToken,
                    'topic' => 'ekskul',
                    'is_answered' => true,
                    'tokens_used' => 85,
                ],
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'bot',
            'topic' => 'ekskul',
            'is_answered' => true,
            'tokens_used' => 85,
        ]);
    }

    public function test_can_retrieve_chat_history(): void
    {
        // Kirim sapaan dulu
        $this->postJson('/api/chat/send', [
            'visitor_token' => $this->visitorToken,
            'message' => 'Halo',
        ]);

        $response = $this->getJson("/api/chat/history/{$this->visitorToken}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'session_id',
                    'visitor_token',
                    'messages',
                ],
            ]);
    }

    public function test_rate_limiter_blocks_excessive_requests(): void
    {
        $customToken = 'spam-test-token-999';

        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/chat/send', [
                'visitor_token' => $customToken,
                'message' => 'Halo',
            ]);
        }

        $response = $this->postJson('/api/chat/send', [
            'visitor_token' => $customToken,
            'message' => 'Halo',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Terlalu banyak permintaan chat dalam waktu singkat. Silakan tunggu sebentar sebelum mengirim pesan kembali.',
            ]);
    }

    public function test_live_gemini_call_when_api_key_is_present(): void
    {
        if (empty(config('services.gemini.api_key'))) {
            $this->markTestSkipped('Gemini API key is not set.');
        }

        $service = app(ChatbotService::class);
        $result = $service->handleQuestion('live-test-visitor-token', 'Apa visi dan misi SMA Widya Darma?');

        $this->assertTrue($result['is_answered']);
        $this->assertEquals('visi_misi', $result['topic']);
        $this->assertNotEmpty($result['bot_response']);
        $this->assertGreaterThan(0, $result['tokens_used']);

        // Bersihkan sesi live test
        ChatSession::where('visitor_token', 'live-test-visitor-token')->delete();
    }
}
