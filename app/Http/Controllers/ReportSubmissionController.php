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
        $request->validate([
            'reportable_type' => ['required', 'string'],
            'reportable_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:10240'], // Max 10MB
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'File laporan wajib dipilih.',
            'file.max' => 'Ukuran file laporan maksimal adalah 10MB.',
            'reportable_type.required' => 'Kategori target laporan tidak valid.',
            'reportable_id.required' => 'Target modul/studi kasus wajib ditentukan.',
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

        // Cek apakah sudah ada laporan sebelumnya untuk target ini
        $existing = ReportSubmission::where('user_id', $userId)
            ->where('reportable_type', $targetType)
            ->where('reportable_id', $request->reportable_id)
            ->first();

        // Jika sudah dinilai, tolak upload ulang
        if ($existing && $existing->status === 'graded') {
            return back()->withErrors([
                'file' => "Laporan untuk materi ini sudah dinilai oleh pengajar (Nilai: {$existing->grade}) dan tidak dapat diubah kembali."
            ]);
        }

        // Hapus file fisik lama jika ada sebelum menimpa dengan file baru
        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        ## PERHATIAN (KOYEB EPHEMERAL STORAGE NOTICE):
        ## File laporan disimpan di local disk container (storage/app/public/laporan/{user_id}/).
        ## Filesystem Koyeb bersifat SEMENTARA (ephemeral), berkas bisa hilang saat container restart/redeploy.
        ## Ini adalah konfigurasi versi testing/demo. Untuk lingkungan produksi permanen, ganti disk storage
        ## ke Cloud Storage seperti Amazon S3, Cloudflare R2, atau Supabase Storage.
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
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        $message = $existing
            ? "Laporan praktikum untuk '{$targetModel->title}' berhasil diperbarui!"
            : "Laporan praktikum untuk '{$targetModel->title}' berhasil diunggah!";

        return redirect()->route('laporan.saya')->with('success', $message);
    }
}
