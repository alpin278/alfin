<?php

namespace App\Http\Controllers;

use App\Models\ReportSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminReportSubmissionController extends Controller
{
    /**
     * Tampilkan semua laporan praktikum masuk dari semua mahasiswa.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');

        $query = ReportSubmission::with(['user', 'reportable', 'gradedByTeacher'])
            ->latest('submitted_at');

        // Filter status
        if ($statusFilter === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($statusFilter === 'graded') {
            $query->where('status', 'graded');
        }

        // Filter search (Nama, NIM, Judul File)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(15)->withQueryString();

        // Hitung statistik untuk badge & tab filter
        $totalCount = ReportSubmission::count();
        $submittedCount = ReportSubmission::where('status', 'submitted')->count();
        $gradedCount = ReportSubmission::where('status', 'graded')->count();

        return view('admin.laporan.index', compact(
            'submissions',
            'statusFilter',
            'search',
            'totalCount',
            'submittedCount',
            'gradedCount'
        ));
    }

    /**
     * Detail 1 laporan praktikum beserta preview dokumen dan form penilaian.
     */
    public function show(int $id): View
    {
        $submission = ReportSubmission::with(['user', 'reportable', 'gradedByTeacher'])
            ->findOrFail($id);

        return view('admin.laporan.show', compact('submission'));
    }

    /**
     * Simpan nilai dan umpan balik (feedback) dari guru/dosen.
     */
    public function grade(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'teacher_feedback' => ['nullable', 'string', 'max:3000'],
        ], [
            'grade.required' => 'Nilai laporan wajib diisi.',
            'grade.numeric' => 'Nilai harus berupa angka.',
            'grade.min' => 'Nilai minimal adalah 0.',
            'grade.max' => 'Nilai maksimal adalah 100.',
        ]);

        $submission = ReportSubmission::findOrFail($id);

        $submission->update([
            'grade' => $request->grade,
            'teacher_feedback' => $request->teacher_feedback,
            'status' => 'graded',
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        return redirect()->route('admin.laporan.show', $id)
            ->with('success', "Penilaian untuk {$submission->user->name} berhasil disimpan!");
    }
}
