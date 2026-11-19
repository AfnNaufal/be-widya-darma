<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Daftar seluruh guru untuk tabel Admin (AdminTeacherTable.tsx).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::with('admin:admin_id,name')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $teachers = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $teachers,
        ]);
    }

    /**
     * Tambah data guru baru (foto otomatis dikompres ke .webp).
     */
    public function store(TeacherRequest $request): JsonResponse
    {
        $data = $request->validatedWithWebp('teachers', 'image', 'image_url');

        if (isset($data['order']) && ! isset($data['sort_order'])) {
            $data['sort_order'] = $data['order'];
            unset($data['order']);
        }

        $teacher = Teacher::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil ditambahkan.',
            'data' => $teacher,
        ], 201);
    }

    /**
     * Detail data guru.
     */
    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load('admin:admin_id,name');

        return response()->json([
            'success' => true,
            'data' => $teacher,
        ]);
    }

    /**
     * Perbarui data guru (foto baru dikompres ke .webp & foto lama dihapus otomatis).
     */
    public function update(TeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validatedWithWebp('teachers', 'image', 'image_url');

        if (isset($data['order']) && ! isset($data['sort_order'])) {
            $data['sort_order'] = $data['order'];
            unset($data['order']);
        }

        $teacher->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diperbarui.',
            'data' => $teacher->fresh(),
        ]);
    }

    /**
     * Hapus data guru.
     */
    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil dihapus.',
        ]);
    }
}
