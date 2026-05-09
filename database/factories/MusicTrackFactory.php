<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MusicTrack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MusicTrack>
 */
final class MusicTrackFactory extends Factory
{
    protected $model = MusicTrack::class;

    public function definition(): array
    {
        return [
            'uploaded_by' => null,
            'title' => $this->faker->randomElement([
                'Wedding March', 'Canon in D', 'A Thousand Years',
                'Perfect', 'Marry You', 'River Flows in You',
            ]),
            'artist' => $this->faker->randomElement([
                'Mendelssohn', 'Pachelbel', 'Christina Perri',
                'Ed Sheeran', 'Bruno Mars', 'Yiruma',
            ]),
            'file_path' => 'tracks/'.(string) Str::ulid().'.mp3',
            'duration_seconds' => $this->faker->numberBetween(120, 360),
        ];
    }
}
