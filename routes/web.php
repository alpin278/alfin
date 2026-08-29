<?php

use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AdminReportSubmissionController;
use App\Http\Controllers\CaseStudyController;
use App\Http\Controllers\ModuleProgressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('beranda');
});

Route::middleware('auth')->group(function () {
    // 1. Beranda Utama (Landing Page Mahasiswa / User)
    Route::get('/beranda', function () {
        return view('beranda');
    })->name('beranda');

    Route::get('/dashboard', function () {
        return redirect()->route('beranda');
    })->name('dashboard');

    // 2. Modul Pembelajaran & Progress
    Route::get('/materi', [ModuleProgressController::class, 'index'])->name('materi');
    Route::get('/dashboard-progress', [ModuleProgressController::class, 'index'])->name('dashboard.progress');

    // 3. Simulator Praktikum
    Route::get('/simulasi', function () {
        return view('simulasi');
    })->name('simulasi');

    // 4. Studi Kasus (Problem-Based Learning)
    Route::get('/studi-kasus', [CaseStudyController::class, 'index'])->name('studi-kasus');
    Route::get('/api/case-studies/{id}', [CaseStudyController::class, 'show'])->name('case-studies.show');
    Route::post('/api/case-studies', [CaseStudyController::class, 'store'])->middleware('admin')->name('case-studies.store');

    // 5. API Update Progress Modul
    Route::post('/api/progress/{moduleId}', [ModuleProgressController::class, 'updateStatus'])->name('progress.update');

    // 6. Laporan Praktikum Mahasiswa
    Route::get('/laporan-saya', [ReportSubmissionController::class, 'index'])->name('laporan.saya');
    Route::post('/laporan-saya/upload', [ReportSubmissionController::class, 'store'])->name('laporan.upload');
    Route::post('/laporan/{id}/ajukan-edit', [ReportSubmissionController::class, 'requestEdit'])->name('laporan.request_edit');

    // Profile Routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Only Routes (Kelola Modul CRUD & Penilaian Laporan)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/daftar-modul-siswa', [AdminModuleController::class, 'studentView'])->name('student_modules.index');
    Route::resource('modules', AdminModuleController::class);
    Route::get('/laporan', [AdminReportSubmissionController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{id}', [AdminReportSubmissionController::class, 'show'])->name('laporan.show');
    Route::post('/laporan/{id}/nilai', [AdminReportSubmissionController::class, 'grade'])->name('laporan.grade');
    Route::post('/laporan/{id}/approve-edit', [AdminReportSubmissionController::class, 'approveEdit'])->name('laporan.approve_edit');
    Route::post('/laporan/{id}/reject-edit', [AdminReportSubmissionController::class, 'rejectEdit'])->name('laporan.reject_edit');
    Route::delete('/laporan/{id}', [AdminReportSubmissionController::class, 'destroy'])->name('laporan.destroy');

    Route::get('/dashboard', function () {
        return redirect()->route('admin.modules.index');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
