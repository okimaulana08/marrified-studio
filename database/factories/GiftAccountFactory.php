<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GiftAccount;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftAccount>
 */
final class GiftAccountFactory extends Factory
{
    protected $model = GiftAccount::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'type' => 'bank',
            'bank_name' => $this->faker->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI']),
            'account_number' => $this->faker->numerify('##########'),
            'account_name' => $this->faker->name(),
            'sort_order' => 0,
        ];
    }
}
