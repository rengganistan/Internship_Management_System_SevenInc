<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Download;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Set all blank or null codes to a placeholder (or delete them) 
        DB::table('downloads')
          ->whereNull('code')
          ->orWhere('code', '')
          ->update(['code' => DB::raw("CONCAT('TMP-', id)")]); // e.g. TMP-1, TMP-2

        // 2) Remove duplicates (keeping one)
        $duplicates = DB::table('downloads')
                        ->select('code', DB::raw('COUNT(*) as c'))
                        ->groupBy('code')
                        ->having('c', '>', 1)
                        ->get();
        foreach ($duplicates as $dup) {
            $rows = DB::table('downloads')
                      ->where('code', $dup->code)
                      ->orderBy('id')
                      ->get()
                      ->pluck('id')
                      ->toArray();
            array_shift($rows); // keep first id
            DB::table('downloads')
              ->whereIn('id', $rows)
              ->delete();
        }

        // 3) Now you can safely add unique index (only if not already exists)
        Schema::table('downloads', function ($table) {
            $table->string('code')->nullable(false)->change();
        });

        $indexExists = collect(DB::select("SHOW INDEX FROM `downloads` WHERE Key_name = 'downloads_code_unique'"))->isNotEmpty();
        if (!$indexExists) {
            Schema::table('downloads', function ($table) {
                $table->unique('code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('downloads', function ($table) {
            $table->dropUnique('downloads_code_unique');
        });
    }
};
