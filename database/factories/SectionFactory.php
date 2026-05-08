<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
final class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'type' => 'cover',
            'variant' => 'arch',
            'sort_order' => 0,
            'enabled' => true,
            'content' => null,
            'decoration_overrides' => null,
        ];
    }
}
