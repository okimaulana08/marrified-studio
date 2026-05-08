<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GuestbookMessage;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestbookMessage>
 */
final class GuestbookMessageFactory extends Factory
{
    protected $model = GuestbookMessage::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => $this->faker->name(),
            'message' => $this->faker->sentence(12),
            'is_visible' => true,
        ];
    }
}
