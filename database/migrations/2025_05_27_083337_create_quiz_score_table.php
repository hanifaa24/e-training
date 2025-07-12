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
        Schema::create('quiz_score', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher')->constrained('employees');
            $table->decimal('score', 5, 2);
            $table->foreignId('material_id')->constrained('materials');
            $table->foreignId('course_id')->constrained('courses');
            $table->boolean('status'); // passed / failed
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_score');
    }
};
