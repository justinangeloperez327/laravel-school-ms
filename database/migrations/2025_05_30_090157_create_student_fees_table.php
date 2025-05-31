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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Total fee amount
            $table->foreignId('fee_category_id')->constrained('fee_categories')->onDelete('cascade');
            $table->date('due_date'); // Due date for the fee payment
            $table->date('paid_date')->nullable(); // Date when the fee was paid
            $table->string('status')->default('unpaid'); // unpaid, paid, overdue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
