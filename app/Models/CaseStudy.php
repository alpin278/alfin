<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'circuit_data',
        'created_by',
    ];

    /**
     * User / Guru yang membuat studi kasus ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi Polymorphic ke Laporan Praktikum
     */
    public function reportSubmissions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ReportSubmission::class, 'reportable');
    }
}

