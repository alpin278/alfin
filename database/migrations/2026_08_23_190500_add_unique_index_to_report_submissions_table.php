<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pembersihan Data Duplikat (Menjaga data paling baru / ID terbesar untuk tiap user_id + reportable_type + reportable_id)
        $duplicates = DB::table('report_submissions')
            ->select('user_id', 'reportable_type', 'reportable_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(id) as keep_id'))
            ->groupBy('user_id', 'reportable_type', 'reportable_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $oldSubmissions = DB::table('report_submissions')
                ->where('user_id', $dup->user_id)
                ->where('reportable_type', $dup->reportable_type)
                ->where('reportable_id', $dup->reportable_id)
                ->where('id', '<', $dup->keep_id)
                ->get();

            foreach ($oldSubmissions as $old) {
                if ($old->file_path && Storage::disk('public')->exists($old->file_path)) {
                    Storage::disk('public')->delete($old->file_path);
                }
            }

            DB::table('report_submissions')
                ->where('user_id', $dup->user_id)
                ->where('reportable_type', $dup->reportable_type)
                ->where('reportable_id', $dup->reportable_id)
                ->where('id', '<', $dup->keep_id)
                ->delete();
        }

        // 2. Tambahkan Index Unique Constraint pada kombinasi user_id, reportable_type, reportable_id
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->unique(['user_id', 'reportable_type', 'reportable_id'], 'unique_user_reportable_submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->dropUnique('unique_user_reportable_submission');
        });
    }
};
