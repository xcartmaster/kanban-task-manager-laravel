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
        Schema::table('boards', function (Blueprint $table) {
            // Correct way: change only the column definition and its comment without rewriting constraints
            $table->foreignId('user_id')->change()->comment('The creator and account owner of this board. Determines whose account limits and storage subscription are applied.');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            // To roll back, we change the comment back to an empty string or remove it entirely
            $table->foreignId('user_id')->change()->comment('');
        });
    }
};
