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
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert changes: make email and password non-nullable again.
            // IMPORTANT: If there are actual NULL values in the database from when they were nullable,
            // this 'down' migration will fail. You would need to populate those NULLs with default values
            // before running 'migrate:rollback'.
            
            // To ensure unique() can be re-added, you might need to drop the index first if it causes issues.
            // For now, we only revert the nullable status.
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};