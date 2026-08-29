<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reportable_type',
        'reportable_id',
        'file_path',
        'original_filename',
        'file_size',
        'file_extension',
        'note',
        'status',
        'edit_request_status',
        'edit_deadline',
        'edit_requested_at',
        'grade',
        'teacher_feedback',
        'graded_by',
        'graded_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'float',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'edit_deadline' => 'datetime',
            'edit_requested_at' => 'datetime',
        ];
    }

    /**
     * Cek apakah batas waktu edit (deadline) telah lewat.
     */
    public function isEditDeadlinePassed(): bool
    {
        if (!$this->edit_deadline) {
            return false;
        }
        return now()->greaterThan($this->edit_deadline);
    }

    /**
     * Auto-expire status jika batas deadline telah terlewati secara real-time.
     */
    public function checkAndExpireEditRequest(): bool
    {
        if ($this->edit_request_status === 'approved' && $this->isEditDeadlinePassed()) {
            $this->edit_request_status = 'expired';
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Cek apakah mahasiswa saat ini diizinkan mengunggah/mengedit berkas laporan.
     */
    public function canStudentUpload(): bool
    {
        // 1. Jika permohonan edit berstatus 'approved': HANYA boleh jika deadline belum lewat
        if ($this->edit_request_status === 'approved') {
            return !$this->isEditDeadlinePassed();
        }

        // 2. Jika permohonan edit berstatus selain 'none' (misal: 'requested', 'rejected', 'expired') -> DILARANG
        if (in_array($this->edit_request_status, ['requested', 'rejected', 'expired'], true)) {
            return false;
        }

        // 3. Jika laporan berstatus 'graded' (sudah dinilai) dan belum ada izin -> DILARANG
        if ($this->status === 'graded') {
            return false;
        }

        // 4. Laporan awal/baru (status 'submitted' dengan edit_request_status 'none') -> Boleh upload
        return true;
    }

    /**
     * Scope untuk otomatis memeriksa dan memperbarui submission yang telah expired tanpa cron job.
     */
    public static function expireOverdueRequests(): int
    {
        return self::where('edit_request_status', 'approved')
            ->whereNotNull('edit_deadline')
            ->where('edit_deadline', '<=', now())
            ->update(['edit_request_status' => 'expired']);
    }

    /**
     * User (mahasiswa) yang mengupload laporan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Polymorphic relation ke Module atau CaseStudy.
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User (guru/admin) yang memberikan nilai.
     */
    public function gradedByTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
