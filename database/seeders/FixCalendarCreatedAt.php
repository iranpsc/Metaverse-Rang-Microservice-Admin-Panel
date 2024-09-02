<?php

namespace Database\Seeders;

use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixCalendarCreatedAt extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Calendar::query()->each(function ($calendar) {
            $calendar->update([
                'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', $calendar->starts_at)->subMinutes(rand(1, 1000))
            ]);
        });
    }
}
