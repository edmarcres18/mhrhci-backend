<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Seed the announcements table with sample data.
     */
    public function run(): void
    {
        if (Announcement::count() >= 10) {
            return;
        }

        Announcement::factory()
            ->count(10)
            ->create();
    }
}
