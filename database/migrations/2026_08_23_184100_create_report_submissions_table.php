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
        Schema::create('report_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');
            $table->index(['reportable_type', 'reportable_id']);
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedInteger('file_size'); // Ukuran dalam KB
            $table->string('file_extension', 20);
            $table->text('note')->nullable();
            $table->string('status')->default('submitted'); // submitted, graded
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_submissions');
    }
};
