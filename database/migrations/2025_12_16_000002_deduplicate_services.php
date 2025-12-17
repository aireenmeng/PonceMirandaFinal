<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. DEDUPLICATE 'Teeth Cleaning'
        // Keep ID 2. Merge IDs 6 and 7 into 2.
        $keeperId = 2;
        $duplicates = [6, 7]; // Based on the list output

        foreach ($duplicates as $duplicateId) {
            // Reassign appointments
            DB::table('appointments')->where('service_id', $duplicateId)->update(['service_id' => $keeperId]);
            // Delete duplicate service
            DB::table('services')->where('id', $duplicateId)->delete();
        }

        // 2. DEDUPLICATE 'Teeth Extraction'
        // 'Tooth Extraction' is ID 3. 'Teeth Extraction' is ID 9.
        // We will keep ID 3 and rename it to 'Teeth Extraction' to match user request.
        // Merge ID 9 into 3.
        $extractionKeeperId = 3;
        $extractionDuplicateId = 9;

        // Reassign appointments from 9 to 3
        DB::table('appointments')->where('service_id', $extractionDuplicateId)->update(['service_id' => $extractionKeeperId]);
        
        // Delete ID 9
        DB::table('services')->where('id', $extractionDuplicateId)->delete();

        // Rename ID 3 to 'Teeth Extraction'
        DB::table('services')->where('id', $extractionKeeperId)->update(['name' => 'Teeth Extraction']);
    }

    public function down(): void
    {
        // Irreversible data merge.
    }
};
