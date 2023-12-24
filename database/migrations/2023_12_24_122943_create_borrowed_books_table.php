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
        Schema::create('borrowed_books', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('borrower');
            $table->foreignId('borrower')->references('id')->on('borrowers');
//            $table->unsignedBigInteger('book');
            $table->foreignId('book')->references('id')->on('books');
            $table->enum('state',['borrowed','overdue','returned'])->default('borrowed');
            $table->boolean('overdue')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('due_date');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_books');
    }
};
