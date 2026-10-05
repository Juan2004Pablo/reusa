<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PublicationCardResource;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    private const TREND_DAYS = 28;

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

        /** @var array<string, int> $availableByModality */
        $availableByModality = Publication::query()
            ->available()
            ->visible()
            ->selectRaw('modality, count(*) as total')
            ->groupBy('modality')
            ->pluck('total', 'modality')
            ->map(fn ($total) => (int) $total)
            ->all();

        // Portada de cada macrocategoría: la foto del objeto disponible más reciente.
        $latestIdByMacro = Publication::query()
            ->available()
            ->visible()
            ->has('images')
            ->join('categories', 'categories.id', '=', 'publications.category_id')
            ->orderByDesc('publications.created_at')
            ->orderByDesc('publications.id')
            ->get(['publications.id', 'categories.parent_id as macro_id'])
            ->unique('macro_id')
            ->pluck('id', 'macro_id');

        $coverByPublication = Publication::query()
            ->with('images')
            ->findMany($latestIdByMacro->values())
            ->mapWithKeys(fn (Publication $publication) => [$publication->id => $publication->images->first()?->url()]);

        $categories = collect(CategoryResource::collection(Category::query()->roots()->orderBy('sort_order')->get())->resolve())
            ->map(fn (array $category) => [
                ...$category,
                'available_count' => $availableByMacro[$category['id']] ?? 0,
                'cover_url' => $coverByPublication[$latestIdByMacro[$category['id']] ?? null] ?? null,
            ])
            ->all();

        return Inertia::render('welcome', [
            'latest' => PublicationCardResource::collection($latest)->resolve(),
            'categories' => $categories,
            'availableCount' => Publication::query()->available()->visible()->count(),
            'stats' => [
                'available' => Publication::query()->available()->visible()->count(),
                // Objetos disponibles por modalidad, en el orden del enum.
                'modalities' => collect(PublicationModality::cases())->map(fn (PublicationModality $modality) => [
                    'value' => $modality->value,
                    'label' => $modality->label(),
                    'total' => $availableByModality[$modality->value] ?? 0,
                ])->all(),
                // Objetos que ya encontraron un nuevo hogar (entregados o vendidos).
                'rehomed' => Publication::query()->visible()
                    ->whereIn('status', [PublicationStatus::Delivered, PublicationStatus::Sold])->count(),
                // Todo lo que se ha publicado y sigue visible, en cualquier estado.
                'published' => Publication::query()->visible()->count(),
                'neighbors' => User::query()->where('is_active', true)->count(),
                // Vecinos activos que ya publicaron al menos un objeto.
                'publishers' => Publication::query()->visible()
                    ->whereHas('user', fn ($user) => $user->where('is_active', true))
                    ->distinct()
                    ->count('user_id'),
                'trends' => [
                    // Objetos publicados por día.
                    'published' => $this->dailyTrend(Publication::query()->visible(), 'created_at'),
                    // Objetos que cambiaron a entregado o vendido ese día.
                    'rehomed' => $this->dailyTrend(
                        Publication::query()->visible()->whereIn('status', [PublicationStatus::Delivered, PublicationStatus::Sold]),
                        'updated_at',
                    ),
                    // Vecinos que se unieron ese día.
                    'neighbors' => $this->dailyTrend(User::query()->where('is_active', true), 'created_at'),
                ],
            ],
        ]);
    }

    /**
     * Conteo diario de los últimos días (el más antiguo primero), con ceros en los días sin actividad.
     *
     * @param  Builder<*>  $query
     * @return list<int>
     */
    private function dailyTrend(Builder $query, string $column): array
    {
        $since = now()->subDays(self::TREND_DAYS - 1)->startOfDay();

        /** @var array<string, int> $perDay */
        $perDay = $query
            ->where($column, '>=', $since)
            ->get([$column])
            ->countBy(fn ($row) => Carbon::parse($row->{$column})->toDateString())
            ->all();

        return collect(range(0, self::TREND_DAYS - 1))
            ->map(fn (int $offset) => $perDay[$since->copy()->addDays($offset)->toDateString()] ?? 0)
            ->all();
    }
}
