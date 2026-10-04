<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publication;
use App\Models\PublicationImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicationImage>
 */
class PublicationImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'path' => 'publications/factory/'.Str::uuid().'.jpg',
            'position' => 0,
        ];
    }
}
