<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->leaf(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'description' => fake()->paragraph(3),
            'modality' => PublicationModality::Donation,
            'condition' => fake()->randomElement(ItemCondition::cases()),
            'price' => null,
            'wanted_in_exchange' => null,
            'location' => fake()->randomElement(['Laureles', 'Belén', 'El Poblado', 'Robledo', 'Envigado']).', Medellín',
            'status' => PublicationStatus::Available,
        ];
    }

    public function donation(): static
    {
        return $this->state(fn () => [
            'modality' => PublicationModality::Donation,
            'price' => null,
            'wanted_in_exchange' => null,
        ]);
    }

    public function exchange(?string $wanted = 'Algo de interés similar'): static
    {
        return $this->state(fn () => [
            'modality' => PublicationModality::Exchange,
            'price' => null,
            'wanted_in_exchange' => $wanted,
        ]);
    }

    public function sale(int $price = 50000): static
    {
        return $this->state(fn () => [
            'modality' => PublicationModality::Sale,
            'price' => $price,
            'wanted_in_exchange' => null,
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Reserved]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Delivered]);
    }

    public function sold(): static
    {
        return $this->sale()->state(fn () => ['status' => PublicationStatus::Sold]);
    }

    public function hidden(?string $reason = null, ?User $by = null): static
    {
        return $this->state(fn () => [
            'hidden_at' => now(),
            'hidden_reason' => $reason,
            'hidden_by' => $by?->id,
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function inCategory(Category $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }

    /**
     * Crea registros de imagen (sin archivos reales en disco).
     */
    public function withImages(int $count = 1): static
    {
        return $this->afterCreating(function (Publication $publication) use ($count): void {
            for ($position = 0; $position < $count; $position++) {
                PublicationImage::factory()->for($publication)->create(['position' => $position]);
            }
        });
    }
}
