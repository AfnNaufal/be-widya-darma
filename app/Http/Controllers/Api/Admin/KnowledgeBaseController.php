<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeBaseRequest;
use App\Models\KnowledgeBase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    /**
     * Daftar seluruh artikel Knowledge Base (bisa filter kategori / status aktif).
     */
    public function index(Request $request): JsonResponse
    {
        $query = KnowledgeBase::with('admin:admin_id,name')
            ->withCount('chatMessages')
            ->orderByDesc('updated_at');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $items = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Tambah item Knowledge Base baru untuk AI Chatbot.
     */
    public function store(KnowledgeBaseRequest $request): JsonResponse
    {
        $kb = KnowledgeBase::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data Knowledge Base berhasil ditambahkan.',
            'data' => $kb,
        ], 201);
    }

    /**
     * Detail item Knowledge Base.
     */
    public function show(KnowledgeBase $knowledgeBase): JsonResponse
    {
        $knowledgeBase->load('admin:admin_id,name')->loadCount('chatMessages');

        return response()->json([
            'success' => true,
            'data' => $knowledgeBase,
        ]);
    }

    /**
     * Perbarui item Knowledge Base.
     */
    public function update(KnowledgeBaseRequest $request, KnowledgeBase $knowledgeBase): JsonResponse
    {
        $knowledgeBase->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data Knowledge Base berhasil diperbarui.',
            'data' => $knowledgeBase->fresh(),
        ]);
    }

    /**
     * Hapus item Knowledge Base.
     */
    public function destroy(KnowledgeBase $knowledgeBase): JsonResponse
    {
        $knowledgeBase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Knowledge Base berhasil dihapus.',
        ]);
    }
}
