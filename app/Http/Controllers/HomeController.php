<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\PublicationCardResource;
use App\Models\Category;
use App\Models\Publication;
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

        return Inertia::render('welcome', [
            'latest' => PublicationCardResource::collection($latest)->resolve(),
            'categories' => CategoryResource::collection(Category::query()->roots()->orderBy('sort_order')->get())->resolve(),
            'availableCount' => Publication::query()->available()->visible()->count(),
        ]);
    }
}
