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
        Schema::table('board_user', function (Blueprint $table) {
            // We change the length to 32 AND inject the detailed universal comment
            $table->string('role', 32)->change()->comment('The project role inside this specific board: owner (creator/administrator), member (full write and move access), viewer (read-only and commenting access).');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_user', function (Blueprint $table) {
            // To roll back, we restore the original varchar(255) length and the previous short comment
            $table->string('role', 255)->change()->comment('owner/member/viewer');
        });
    }
};
