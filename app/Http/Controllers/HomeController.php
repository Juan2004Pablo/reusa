<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PublicationCardResource;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $latest = Publication::query()
            ->available()
            ->visible()
            ->with(['category', 'images'])
            ->latest()
            ->latest('id')
            ->limit(8)
            ->get();

        // Objetos disponibles por macrocategoría, en una sola consulta.
        /** @var array<int|string, int> $availableByMacro */
        $availableByMacro = Publication::query()
            ->available()
            ->visible()
            ->join('categories', 'categories.id', '=', 'publications.category_id')
            ->selectRaw('categories.parent_id as macro_id, count(*) as total')
            ->groupBy('categories.parent_id')
            ->pluck('total', 'macro_id')
            ->map(fn ($total) => (int) $total)
            ->all();

        $categories = collect(CategoryResource::collection(Category::query()->roots()->orderBy('sort_order')->get())->resolve())
            ->map(fn (array $category) => [...$category, 'available_count' => $availableByMacro[$category['id']] ?? 0])
            ->all();

        return Inertia::render('welcome', [
            'latest' => PublicationCardResource::collection($latest)->resolve(),
            'categories' => $categories,
            'availableCount' => Publication::query()->available()->visible()->count(),
            'stats' => [
                'available' => Publication::query()->available()->visible()->count(),
                // Objetos que ya encontraron un nuevo hogar (entregados o vendidos).
                'rehomed' => Publication::query()->visible()
                    ->whereIn('status', [PublicationStatus::Delivered, PublicationStatus::Sold])->count(),
                'neighbors' => User::query()->where('is_active', true)->count(),
            ],
        ]);
    }
}
