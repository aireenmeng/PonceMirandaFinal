<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('services')
            ->where('name', 'Braces Adjustment')
            ->update(['name' => 'Teeth Whitening']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('services')
            ->where('name', 'Teeth Whitening')
            ->update(['name' => 'Braces Adjustment']);
    }
};
