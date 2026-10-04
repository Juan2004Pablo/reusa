<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Http\Resources\MyPublicationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyPublicationController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $publications = $user->publications()
            ->with(['category', 'images'])
            ->latest()
            ->latest('id')
            ->paginate(15);

        /** @var array<string, int> $counts */
        $counts = $user->publications()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();

        return Inertia::render('my/publications', [
            'publications' => MyPublicationResource::collection($publications),
            'counts' => collect(PublicationStatus::cases())
                ->mapWithKeys(fn (PublicationStatus $status) => [$status->value => $counts[$status->value] ?? 0])
                ->all(),
        ]);
    }
}
