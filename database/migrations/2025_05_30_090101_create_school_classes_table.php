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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Class 1A", "Class 2B"
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('set null'); // Teacher in charge of the class
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade'); // Academic year this class belongs to
            $table->string('section')->nullable(); // e.g., "A", "B", "C" for sections within a class
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
