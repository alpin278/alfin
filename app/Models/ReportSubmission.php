<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportSubmission extends Model
{
    use HasFactory;

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
        ];
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
