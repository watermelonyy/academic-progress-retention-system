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
        Schema::create('student_status_history', function (Blueprint $table) {

            $table->id('status_id');

            $table->unsignedBigInteger('student_id');

            $table->unsignedBigInteger('semester_id');

            $table->string('academic_status');

            $table->decimal('failure_percentage', 5, 2);

            $table->boolean('priority_alert')->default(false);

            $table->text('remarks')->nullable();

            $table->dateTime('created_at');

            // FOREIGN KEYS

            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('semester_id')
                ->references('semester_id')
                ->on('semesters')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
    }
};