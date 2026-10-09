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
        Schema::create('academic_notices', function (Blueprint $table) {

            $table->id('notice_id');

            $table->unsignedBigInteger('student_id');

            $table->unsignedBigInteger('semester_id');

            $table->unsignedBigInteger('status_id');

            $table->string('notice_type');

            $table->string('pdf_path')->nullable();

            $table->dateTime('generated_at');

            $table->timestamps();

            // FOREIGN KEYS

            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('semester_id')
                ->references('semester_id')
                ->on('semesters')
                ->onDelete('cascade');

            $table->foreign('status_id')
                ->references('status_id')
                ->on('student_status_history')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_notices');
    }
};