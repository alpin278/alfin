<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Module;
use App\Models\ReportSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportSubmissionController extends Controller
{
    /**
     * Tampilkan halaman "Laporan Saya" (Daftar modul & studi kasus + status laporan murid).
     */
    public function index(): View
    {
        // 0. Auto-expire permohonan edit yang telah melewati deadline secara real-time
        ReportSubmission::expireOverdueRequests();

        $user = Auth::user();

        // 1. Ambil semua Modul Pembelajaran
        $modules = Module::orderBy('module_number')->get();

        // 2. Ambil semua Studi Kasus (PBL)
        $caseStudies = CaseStudy::latest()->get();

        // 3. Ambil riwayat laporan milik user yang login
        $submissions = ReportSubmission::where('user_id', $user->id)
            ->with(['reportable', 'gradedByTeacher'])
            ->get()
            ->keyBy(function ($item) {
                return $item->reportable_type . '_' . $item->reportable_id;
            });

        // Hitung statistik
        $totalTargets = $modules->count() + $caseStudies->count();
        $submittedCount = $submissions->where('status', 'submitted')->count();
        $gradedCount = $submissions->where('status', 'graded')->count();
        $averageGrade = $gradedCount > 0 ? round($submissions->where('status', 'graded')->avg('grade'), 1) : null;

        return view('laporan-saya', compact(
            'modules',
            'caseStudies',
            'submissions',
            'totalTargets',
            'submittedCount',
            'gradedCount',
            'averageGrade'
        ));
    }

    /**
     * Upload / Upload Ulang Laporan Praktikum.
     */
    public function store(Request $request): RedirectResponse
    {
        // 0. Auto-expire permohonan edit yang telah melewati deadline
        ReportSubmission::expireOverdueRequests();

        $request->validate([
            'reportable_type' => ['required', 'string'],
            'reportable_id' => ['required', 'integer'],
            'file' => [
                'required',
                'file',
                'max:10240', // Maks 10 MB (10240 KB)
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,rar',
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'Berkas laporan wajib diunggah.',
            'file.file' => 'Berkas laporan harus berupa file yang valid.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.mimes' => 'Format file tidak didukung. Format yang diizinkan: PDF, Word (.doc, .docx), PowerPoint (.ppt, .pptx), Excel (.xls, .xlsx), JPG, PNG, ZIP, RAR.',
            'reportable_type.required' => 'Kategori target laporan tidak valid.',
            'reportable_id.required' => 'Target modul/studi kasus wajib ditentukan.',
            'note.max' => 'Catatan laporan maksimal 1000 karakter.',
        ]);

        // Resolusi tipe polymorphic model
        $targetType = match ($request->reportable_type) {
            'module', 'Module', Module::class => Module::class,
            'case_study', 'case', 'CaseStudy', CaseStudy::class => CaseStudy::class,
            default => null,
        };

        if (!$targetType) {
            return back()->withErrors(['reportable_type' => 'Tipe target laporan tidak dikenali.'])->withInput();
        }

        // Verifikasi apakah record modul / studi kasus valid
        $targetModel = $targetType::find($request->reportable_id);
        if (!$targetModel) {
            return back()->withErrors(['reportable_id' => 'Data materi atau studi kasus tidak ditemukan.'])->withInput();
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFilename = $file->getClientOriginalName();
        $fileSizeKb = (int) round($file->getSize() / 1024);

        // DAFTAR EKSTENSI BERBAHAYA (SECURITY BLOCKLIST)
        $dangerousExtensions = [
            'exe', 'php', 'phtml', 'php3', 'php4', 'php5', 'phar',
            'sh', 'bat', 'js', 'jar', 'msi', 'cmd', 'com', 'scr',
            'vbs', 'ps1', 'cgi', 'pl', 'py', 'htm', 'html', 'asp', 'aspx', 'jsp'
        ];

        if (in_array($extension, $dangerousExtensions, true)) {
            return back()->withErrors([
                'file' => "Keamanan: File berekstensi .{$extension} dilarang diunggah. Silakan unggah format dokumen (PDF, Word, Excel, PPT) atau gambar (JPG, PNG)."
            ])->withInput();
        }

        $userId = Auth::id();

        // Cek apakah sudah ada laporan sebelumnya untuk target ini (termasuk yang di-soft-delete)
        $existing = ReportSubmission::withTrashed()
            ->where('user_id', $userId)
            ->where('reportable_type', $targetType)
            ->where('reportable_id', $request->reportable_id)
            ->first();

        // Cek hak akses unggah ulang laporan jika ada laporan aktif (belum dihapus)
        if ($existing && !$existing->trashed()) {
            // 1. Jika permohonan 'approved' tapi sudah melewati deadline: auto-expire & tolak
            if ($existing->edit_request_status === 'approved' && $existing->isEditDeadlinePassed()) {
                $existing->update(['edit_request_status' => 'expired']);
                $deadlineText = $existing->edit_deadline ? $existing->edit_deadline->translatedFormat('d M Y, H:i') . ' WIB' : 'yang ditentukan';
                return back()->withErrors([
                    'file' => "Batas waktu (deadline) upload ulang laporan telah berakhir pada {$deadlineText}. Upload berkas baru ditolak."
                ])->withInput();
            }

            // 2. Jika permohonan 'requested' (masih menunggu approval)
            if ($existing->edit_request_status === 'requested') {
                return back()->withErrors([
                    'file' => "Permintaan izin edit laporan Anda masih menunggu persetujuan pengajar/admin."
                ])->withInput();
            }

            // 3. Jika permohonan 'rejected' (ditolak)
            if ($existing->edit_request_status === 'rejected') {
                return back()->withErrors([
                    'file' => "Permintaan izin edit laporan Anda telah ditolak oleh pengajar. Silakan ajukan izin baru jika diperlukan."
                ])->withInput();
            }

            // 4. Jika permohonan 'expired' (kadaluarsa)
            if ($existing->edit_request_status === 'expired') {
                $deadlineText = $existing->edit_deadline ? $existing->edit_deadline->translatedFormat('d M Y, H:i') . ' WIB' : '';
                return back()->withErrors([
                    'file' => "Batas waktu edit laporan telah berakhir" . ($deadlineText ? " ({$deadlineText})" : "") . ". Anda tidak dapat lagi mengunggah berkas baru."
                ])->withInput();
            }

            // 5. Jika laporan sudah dinilai dan tidak memiliki izin edit yang approved
            if ($existing->status === 'graded' && $existing->edit_request_status !== 'approved') {
                return back()->withErrors([
                    'file' => "Laporan untuk materi ini sudah dinilai oleh pengajar (Nilai: {$existing->grade}). Silakan ajukan izin edit jika ingin mengunggah ulang."
                ])->withInput();
            }

            // 6. Validasi final canStudentUpload
            if (!$existing->canStudentUpload()) {
                return back()->withErrors([
                    'file' => "Anda tidak memiliki izin untuk mengunggah atau mengganti berkas laporan ini."
                ])->withInput();
            }
        }

        // Jika sebelumnya di-soft-delete oleh admin, restore record untuk diisi berkas baru
        if ($existing && $existing->trashed()) {
            $existing->restore();
        }

        // Hapus file fisik lama jika ada sebelum menimpa dengan file baru
        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $storagePath = $file->store("laporan/{$userId}", 'public');

        // Gunakan updateOrCreate dengan kunci pencarian: user_id + reportable_type + reportable_id
        ReportSubmission::updateOrCreate(
            [
                'user_id' => $userId,
                'reportable_type' => $targetType,
                'reportable_id' => $request->reportable_id,
            ],
            [
                'file_path' => $storagePath,
                'original_filename' => $originalFilename,
                'file_size' => $fileSizeKb,
                'file_extension' => $extension,
                'note' => $request->note,
                'status' => 'submitted', // Kembali ke status submitted untuk dinilai
                'edit_request_status' => 'none', // Reset status permohonan edit
                'edit_deadline' => null,
                'edit_requested_at' => null,
                'grade' => null,
                'teacher_feedback' => null,
                'graded_by' => null,
                'graded_at' => null,
                'submitted_at' => now(),
            ]
        );

        $message = $existing
            ? "Laporan praktikum untuk '{$targetModel->title}' berhasil diperbarui dan dikirim kembali!"
            : "Laporan praktikum untuk '{$targetModel->title}' berhasil diunggah!";

        return redirect()->route('laporan.saya')->with('success', $message);
    }

    /**
     * Mahasiswa mengajukan izin edit laporan yang telah dinilai.
     */
    public function requestEdit(int $id): RedirectResponse
    {
        $userId = Auth::id();
        $submission = ReportSubmission::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Validasi: hanya laporan yang sudah dinilai yang perlu izin edit
        if ($submission->status !== 'graded') {
            return redirect()->route('laporan.saya')
                ->with('error', 'Laporan belum dinilai, Anda dapat langsung mengganti berkas laporan.');
        }

        // Validasi: tidak boleh mengajukan jika status permohonan masih requested atau approved aktif
        if ($submission->edit_request_status === 'requested') {
            return redirect()->route('laporan.saya')
                ->with('error', 'Permintaan izin edit laporan ini sedang menunggu persetujuan admin.');
        }

        if ($submission->edit_request_status === 'approved' && !$submission->isEditDeadlinePassed()) {
            return redirect()->route('laporan.saya')
                ->with('error', 'Permintaan izin edit Anda sudah disetujui. Silakan unggah laporan sebelum batas waktu.');
        }

        $submission->update([
            'edit_request_status' => 'requested',
            'edit_requested_at' => now(),
        ]);

        return redirect()->route('laporan.saya')
            ->with('success', "Permintaan izin edit untuk '{$submission->reportable->title}' berhasil diajukan! Silakan tunggu konfirmasi dosen/admin.");
    }
}
