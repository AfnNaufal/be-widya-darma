<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminKnowledgeBaseFeatureTest extends TestCase
{
    protected Admin $admin;

    protected string $plainToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plainToken = Str::random(64);

        $this->admin = Admin::updateOrCreate(
            ['email' => 'admin_test@widyadarma.sch.id'],
            [
                'name' => 'Admin Testing',
                'password' => Hash::make('secret_test_password'),
                'api_token' => hash('sha256', $this->plainToken),
                'api_token_expires_at' => now()->addHours(6),
            ]
        );
    }

    protected function tearDown(): void
    {
        KnowledgeBase::where('title', 'LIKE', '%[TEST]%')->delete();
        $this->admin->delete();

        parent::tearDown();
    }

    public function test_unauthorized_user_cannot_access_knowledge_bases(): void
    {
        $response = $this->getJson('/api/admin/knowledge-bases');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated. Token tidak valid atau sudah kedaluwarsa.',
            ]);
    }

    public function test_authenticated_admin_can_list_knowledge_bases(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->getJson('/api/admin/knowledge-bases');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ]);
    }

    public function test_authenticated_admin_can_create_knowledge_base(): void
    {
        $payload = [
            'category' => 'fasilitas',
            'title' => '[TEST] Laboratorium Komputer Modern',
            'content' => 'SMA Widya Darma memiliki laboratorium komputer berstandar industri dengan koneksi internet cepat.',
            'is_active' => true,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->postJson('/api/admin/knowledge-bases', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data Knowledge Base berhasil ditambahkan.',
                'data' => [
                    'category' => 'fasilitas',
                    'title' => '[TEST] Laboratorium Komputer Modern',
                    'admin_id' => $this->admin->admin_id,
                ],
            ]);

        $this->assertDatabaseHas('knowledge_base', [
            'title' => '[TEST] Laboratorium Komputer Modern',
            'admin_id' => $this->admin->admin_id,
        ]);

        // Verifikasi ActivityLog tercatat
        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $this->admin->admin_id,
            'action' => 'created',
            'target_table' => 'knowledge_base',
        ]);
    }

    public function test_authenticated_admin_can_view_knowledge_base_detail(): void
    {
        $kb = KnowledgeBase::create([
            'admin_id' => $this->admin->admin_id,
            'category' => 'akademik',
            'title' => '[TEST] Kurikulum Merdeka Terintegrasi',
            'content' => 'Penerapan kurikulum merdeka di SMA Widya Darma.',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->getJson("/api/admin/knowledge-bases/{$kb->kb_id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'kb_id' => $kb->kb_id,
                    'title' => '[TEST] Kurikulum Merdeka Terintegrasi',
                ],
            ]);
    }

    public function test_authenticated_admin_can_update_knowledge_base(): void
    {
        $kb = KnowledgeBase::create([
            'admin_id' => $this->admin->admin_id,
            'category' => 'akademik',
            'title' => '[TEST] Info Beasiswa Prestasi',
            'content' => 'Konten awal beasiswa prestasi.',
            'is_active' => true,
        ]);

        $payload = [
            'title' => '[TEST] Info Beasiswa Prestasi Diperbarui',
            'content' => 'Konten beasiswa prestasi telah diperbarui untuk tahun ajaran baru.',
            'is_active' => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->putJson("/api/admin/knowledge-bases/{$kb->kb_id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Knowledge Base berhasil diperbarui.',
                'data' => [
                    'kb_id' => $kb->kb_id,
                    'title' => '[TEST] Info Beasiswa Prestasi Diperbarui',
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('knowledge_base', [
            'kb_id' => $kb->kb_id,
            'title' => '[TEST] Info Beasiswa Prestasi Diperbarui',
            'is_active' => 0,
        ]);
    }

    public function test_authenticated_admin_can_delete_knowledge_base(): void
    {
        $kb = KnowledgeBase::create([
            'admin_id' => $this->admin->admin_id,
            'category' => 'umum',
            'title' => '[TEST] Item yang akan dihapus',
            'content' => 'Konten sementara untuk pengujian hapus.',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->plainToken)
            ->deleteJson("/api/admin/knowledge-bases/{$kb->kb_id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Knowledge Base berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('knowledge_base', [
            'kb_id' => $kb->kb_id,
        ]);
    }
}
