<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Invitation;
use App\Support\GuestToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
final class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => $this->faker->name(),
            'relation' => $this->faker->randomElement(['Bapak', 'Ibu', 'Bapak/Ibu', 'Saudara', 'Saudari']),
            'phone' => $this->faker->numerify('081##########'),
            'token' => GuestToken::generate(),
            'opens_count' => 0,
        ];
    }
}
