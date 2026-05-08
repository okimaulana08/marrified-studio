<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'type' => 'akad',
            'name' => 'Akad Nikah',
            'date' => now()->addMonths(3)->format('Y-m-d'),
            'time' => '08:00:00',
            'venue_name' => 'Masjid Al-Ikhsan',
            'venue_address' => 'Jl. Trunojoyo No. 61, Banjarmasin, Kalimantan Selatan',
            'maps_url' => 'https://maps.google.com/?q=Masjid+Al-Ikhsan',
            'sort_order' => 0,
        ];
    }

    public function akad(): self
    {
        return $this->state(fn () => [
            'type' => 'akad',
            'name' => 'Akad Nikah',
            'time' => '08:00:00',
            'sort_order' => 0,
        ]);
    }

    public function resepsi(): self
    {
        return $this->state(fn () => [
            'type' => 'resepsi',
            'name' => 'Resepsi Pernikahan',
            'time' => '11:00:00',
            'sort_order' => 1,
        ]);
    }
}
