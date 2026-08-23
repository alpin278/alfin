<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CaseStudyController extends Controller
{
    /**
     * Tampilkan daftar studi kasus (PBL) untuk mahasiswa & admin.
     */
    public function index(): View
    {
        $caseStudies = CaseStudy::with('creator')->latest()->get();
        return view('studi-kasus', compact('caseStudies'));
    }

    /**
     * Simpan studi kasus baru dari simulator (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'circuit_data' => 'required',
        ]);

        $circuitData = is_string($validated['circuit_data']) 
            ? $validated['circuit_data'] 
            : json_encode($validated['circuit_data']);

        $caseStudy = CaseStudy::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'circuit_data' => $circuitData,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Studi kasus berhasil disimpan!',
            'data' => $caseStudy,
        ], 201);
    }

    /**
     * Ambil data 1 studi kasus beserta circuit_data untuk simulator.
     */
    public function show(int|string $id): JsonResponse
    {
        $caseStudy = CaseStudy::with('creator')->findOrFail($id);

        $parsedCircuit = is_string($caseStudy->circuit_data)
            ? json_decode($caseStudy->circuit_data, true)
            : $caseStudy->circuit_data;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $caseStudy->id,
                'title' => $caseStudy->title,
                'description' => $caseStudy->description,
                'circuit_data' => $parsedCircuit,
                'creator_name' => $caseStudy->creator?->name ?? 'Dosen/Guru DTE',
                'created_at' => $caseStudy->created_at?->format('d M Y, H:i') ?? '',
            ],
        ]);
    }
}

