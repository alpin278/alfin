<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleProgressController extends Controller
{
    /**
     * Tampilkan semua modul beserta status progress user yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $modules = Module::orderBy('module_number', 'asc')
            ->with(['moduleProgress' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->map(function ($module) {
                $progress = $module->moduleProgress->first();
                $module->user_status = $progress ? $progress->status : 'belum_mulai';
                $module->user_score = $progress ? $progress->last_score : null;
                $module->completed_at = $progress ? $progress->completed_at : null;
                return $module;
            });

        return view('materi', compact('modules'));
    }

    /**
     * Update status progress modul untuk user yang login.
     */
    public function updateStatus(Request $request, int $moduleId): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:belum_mulai,sedang_berjalan,selesai',
            'last_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        $status = $request->input('status');

        $data = [
            'status' => $status,
        ];

        if ($request->has('last_score')) {
            $data['last_score'] = $request->input('last_score');
        }

        if ($status === 'selesai') {
            $data['completed_at'] = now();
        }

        $progress = ModuleProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $moduleId,
            ],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Status progress modul berhasil diperbarui.',
            'data' => $progress,
        ]);
    }
}
