<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->string('edit_request_status')->default('none')->after('status'); // none, requested, approved, rejected, expired
            $table->dateTime('edit_deadline')->nullable()->after('edit_request_status');
            $table->timestamp('edit_requested_at')->nullable()->after('edit_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->dropColumn(['edit_request_status', 'edit_deadline', 'edit_requested_at']);
        });
    }
};
