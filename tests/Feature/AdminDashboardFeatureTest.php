<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardFeatureTest extends TestCase
{
    protected Admin $admin;

    protected string $plainToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plainToken = Str::random(64);

        $this->admin = Admin::updateOrCreate(
            ['email' => 'dashboard_test@widyadarma.sch.id'],
            [
                'name' => 'Admin Dashboard Tester',
                'password' => Hash::make('secret_test_password'),
                'api_token' => hash('sha256', $this->plainToken),
                'api_token_expires_at' => now()->addHours(6),
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->admin->delete();

        parent::tearDown();
    }

    public function test_unauthenticated_cannot_access_dashboard_stats(): void
    {
        $this->getJson('/api/admin/dashboard/stats')->assertStatus(401);
        $this->getJson('/api/admin/dashboard/chatbot-stats')->assertStatus(401);
    }

    public function test_authenticated_admin_can_get_content_stats(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->getJson('/api/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'news' => ['total', 'published', 'draft'],
                    'programs' => ['total'],
                    'extracurriculars' => ['total'],
                    'galleries' => ['total'],
                    'knowledge_base' => ['total', 'active'],
                    'spmb' => ['is_active', 'active_academic_year'],
                    'website_traffic' => ['total_page_views'],
                ],
            ]);
    }

    public function test_authenticated_admin_can_get_chatbot_stats(): void
    {
        // Buat dummy chat session & message untuk memastikan statistik terhitung
        $session = ChatSession::create([
            'visitor_token' => 'dummy-stats-token',
            'started_at' => now(),
        ]);

        ChatMessage::create([
            'session_id' => $session->session_id,
            'sender' => 'user',
            'message' => 'Pertanyaan statistik test',
            'topic' => null,
            'is_answered' => false,
            'tokens_used' => 0,
            'created_at' => now(),
        ]);

        ChatMessage::create([
            'session_id' => $session->session_id,
            'sender' => 'bot',
            'message' => 'Jawaban statistik test',
            'topic' => 'spmb',
            'is_answered' => true,
            'tokens_used' => 45,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->getJson('/api/admin/dashboard/chatbot-stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'total_sessions',
                        'total_user_questions',
                        'total_bot_responses',
                        'answered_count',
                        'unanswered_count',
                        'answered_rate_percentage',
                        'total_tokens_used',
                    ],
                    'popular_topics',
                    'recent_unanswered_questions',
                ],
            ]);

        // Bersihkan data dummy
        $session->delete();
    }
}
