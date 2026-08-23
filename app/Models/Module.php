<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'module_number',
        'icon',
    ];

    public function moduleProgress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    /**
     * Relasi Polymorphic ke Laporan Praktikum
     */
    public function reportSubmissions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ReportSubmission::class, 'reportable');
    }
}
