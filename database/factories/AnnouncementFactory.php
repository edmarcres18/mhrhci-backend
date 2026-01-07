<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6, true);

        return [
            'title' => $title,
            'description' => $this->faker->paragraphs(2, true),
            // Optionally ensure uniqueness-ish for convenience
            'created_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'updated_at' => now(),
        ];
    }
}
