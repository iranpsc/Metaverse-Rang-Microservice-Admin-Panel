<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReplaceImagesUrlSeeder extends Seeder
{
    private const OLD_URL = 'https://api.rgb.irpsc.com';

    private const NEW_URL = 'https://api.metarang.com';

    /**
     * Replace old admin URL with new one in images.url column.
     */
    public function run(): void
    {
        $updated = DB::table('kycs')
            ->where('melli_card', 'like', '%' . self::OLD_URL . '%')
            ->update([
                'melli_card' => DB::raw(
                    "REPLACE(melli_card, '" . self::OLD_URL . "', '" . self::NEW_URL . "')"
                ),
            ]);

        $this->command->info("Updated {$updated} image URL(s).");
    }
}
