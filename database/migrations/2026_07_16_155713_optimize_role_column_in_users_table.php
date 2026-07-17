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
        Schema::table('users', function (Blueprint $table) {
            // Optimize column size to varchar(32) and inject professional documentation comment
            $table->string('role', 32)->default('user')->change()->comment('The global application role: admin (super administrator), manager (moderator assistant), user (regular SaaS client).');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore original varchar(255) length on rollback
            $table->string('role', 255)->default('user')->change()->comment('');
        });
    }
};
