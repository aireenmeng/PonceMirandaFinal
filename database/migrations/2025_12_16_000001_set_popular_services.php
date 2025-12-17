<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename 'Teeth Whitening' (formerly Braces Adjustment) to 'Teeth Cleaning'
        DB::table('services')
            ->where('name', 'Teeth Whitening')
            ->update(['name' => 'Teeth Cleaning']);

        // 2. Ensure 'Teeth Extraction' exists
        $exists = DB::table('services')->where('name', 'Teeth Extraction')->exists();
        
        if (!$exists) {
            DB::table('services')->insert([
                'name' => 'Teeth Extraction',
                'description' => 'Safe and painless removal of problematic teeth.',
                'price' => 1500.00, // Estimated price
                'duration_minutes' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('services')
            ->where('name', 'Teeth Cleaning')
            ->update(['name' => 'Teeth Whitening']);
            
        // We don't delete 'Teeth Extraction' to avoid data loss if it was used.
    }
};
