<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Couple;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Couple>
 */
final class CoupleFactory extends Factory
{
    protected $model = Couple::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'bride_name' => $this->faker->firstNameFemale().' '.$this->faker->lastName(),
            'bride_nickname' => $this->faker->firstNameFemale(),
            'bride_parents' => 'Bapak '.$this->faker->name('male').' & Ibu '.$this->faker->name('female'),
            'bride_instagram' => '@'.$this->faker->userName(),
            'groom_name' => $this->faker->firstNameMale().' '.$this->faker->lastName(),
            'groom_nickname' => $this->faker->firstNameMale(),
            'groom_parents' => 'Bapak '.$this->faker->name('male').' & Ibu '.$this->faker->name('female'),
            'groom_instagram' => '@'.$this->faker->userName(),
        ];
    }
}
