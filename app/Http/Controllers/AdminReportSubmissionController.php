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
        // Auto-expire permohonan edit yang telah melewati deadline secara real-time
        ReportSubmission::expireOverdueRequests();

        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');

        $query = ReportSubmission::with(['user', 'reportable', 'gradedByTeacher'])
            ->latest('submitted_at');

        // Filter status
        if ($statusFilter === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($statusFilter === 'graded') {
            $query->where('status', 'graded');
        } elseif ($statusFilter === 'requested') {
            $query->where('edit_request_status', 'requested');
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
        $editRequestsCount = ReportSubmission::where('edit_request_status', 'requested')->count();

        return view('admin.laporan.index', compact(
            'submissions',
            'statusFilter',
            'search',
            'totalCount',
            'submittedCount',
            'gradedCount',
            'editRequestsCount'
        ));
    }

    /**
     * Detail 1 laporan praktikum beserta preview dokumen dan form penilaian.
     */
    public function show(int $id): View
    {
        ReportSubmission::expireOverdueRequests();

        $submission = ReportSubmission::with(['user', 'reportable', 'gradedByTeacher'])
            ->findOrFail($id);

        $submission->checkAndExpireEditRequest();

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
            'edit_request_status' => 'none', // Reset status edit saat sudah dinilai
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        return redirect()->route('admin.laporan.show', $id)
            ->with('success', "Penilaian untuk {$submission->user->name} berhasil disimpan!");
    }

    /**
     * Setujui permohonan edit laporan dan tetapkan batas waktu (deadline).
     */
    public function approveEdit(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'edit_deadline' => ['required', 'date', 'after:now'],
        ], [
            'edit_deadline.required' => 'Batas waktu (deadline) upload ulang wajib ditentukan.',
            'edit_deadline.date' => 'Format tanggal batas waktu tidak valid.',
            'edit_deadline.after' => 'Batas waktu harus merupakan waktu di masa mendatang.',
        ]);

        $submission = ReportSubmission::findOrFail($id);

        // Reset nilai menjadi NULL dan update status permohonan edit
        $submission->update([
            'edit_request_status' => 'approved',
            'edit_deadline' => $request->edit_deadline,
            'grade' => null,
            'graded_at' => null,
            'graded_by' => null,
        ]);

        return redirect()->route('admin.laporan.show', $id)
            ->with('success', "Permintaan edit untuk {$submission->user->name} disetujui! Batas waktu upload ulang telah ditetapkan hingga " . \Carbon\Carbon::parse($request->edit_deadline)->translatedFormat('d M Y, H:i') . " WIB.");
    }

    /**
     * Tolak permohonan edit laporan.
     */
    public function rejectEdit(Request $request, int $id): RedirectResponse
    {
        $submission = ReportSubmission::findOrFail($id);

        $submission->update([
            'edit_request_status' => 'rejected',
            'edit_deadline' => null,
        ]);

        return redirect()->route('admin.laporan.show', $id)
            ->with('success', "Permintaan izin edit untuk {$submission->user->name} telah ditolak.");
    }

    /**
     * Soft delete laporan mahasiswa (khusus Admin).
     * Berkas fisik tetap disimpan di storage sebagai arsip/backup.
     */
    public function destroy(int $id): RedirectResponse
    {
        $submission = ReportSubmission::findOrFail($id);
        $studentName = $submission->user->name ?? 'Mahasiswa';
        $reportTitle = $submission->reportable->title ?? 'Praktikum';

        $submission->delete();

        return redirect()->route('admin.laporan.index')
            ->with('success', "Laporan {$reportTitle} milik {$studentName} berhasil dihapus.");
    }
}
