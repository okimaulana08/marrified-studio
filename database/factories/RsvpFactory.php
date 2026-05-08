<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rsvp>
 */
final class RsvpFactory extends Factory
{
    protected $model = Rsvp::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('081##########'),
            'attendance' => $this->faker->randomElement(['attending', 'not_attending', 'maybe']),
            'party_size' => $this->faker->numberBetween(1, 4),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
