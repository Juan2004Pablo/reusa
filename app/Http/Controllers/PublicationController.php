<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Publications\CreatePublication;
use App\Actions\Publications\DeletePublication;
use App\Actions\Publications\UpdatePublication;
use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;
use App\Http\Requests\Publications\CatalogRequest;
use App\Http\Requests\Publications\StorePublicationRequest;
use App\Http\Requests\Publications\UpdatePublicationRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PublicationCardResource;
use App\Http\Resources\PublicationDetailResource;
use App\Http\Resources\PublicationFormResource;
use App\Models\Category;
use App\Models\Publication;
use App\Support\EnumOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    /**
     * Catálogo público con búsqueda, filtros, orden y paginación.
     */
    public function index(CatalogRequest $request): Response
    {
        $filters = $request->filters();

        $publications = Publication::query()
            ->visible()
            ->filter($filters)
            ->with(['category', 'images'])
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('publications/index', [
            'publications' => PublicationCardResource::collection($publications),
            'filters' => $filters->toArray(),
            'categories' => CategoryResource::collection(Category::tree())->resolve(),
            'options' => [
                'modalities' => EnumOptions::from(PublicationModality::cases()),
                'statuses' => EnumOptions::from(PublicationStatus::cases()),
                'sorts' => EnumOptions::from(PublicationSort::cases()),
            ],
        ]);
    }

    public function show(Request $request, Publication $publication): Response
    {
        // Una publicación oculta se comporta como inexistente para terceros.
        abort_unless(Gate::allows('view', $publication), 404);

        $publication->load(['category.parent', 'images', 'user']);

        return Inertia::render('publications/show', [
            'publication' => (new PublicationDetailResource($publication))->resolve(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Publication::class);

        return Inertia::render('publications/create', $this->formProps());
    }

    public function store(StorePublicationRequest $request, CreatePublication $action): RedirectResponse
    {
        $publication = $action->execute($request->user(), $request->publicationData(), $request->imageOrder(), $request->uploads());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('messages.publications.created')]);

        return to_route('publications.show', $publication);
    }

    public function edit(Publication $publication): Response
    {
        Gate::authorize('update', $publication);

        $publication->load(['category', 'images']);

        return Inertia::render('publications/edit', [
            ...$this->formProps(),
            'publication' => (new PublicationFormResource($publication))->resolve(),
        ]);
    }

    public function update(UpdatePublicationRequest $request, Publication $publication, UpdatePublication $action): RedirectResponse
    {
        $action->execute($publication, $request->publicationData(), $request->imageOrder(), $request->uploads());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('messages.publications.updated')]);

        return to_route('publications.show', $publication);
    }

    public function destroy(Publication $publication, DeletePublication $action): RedirectResponse
    {
        Gate::authorize('delete', $publication);

        $action->execute($publication);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('messages.publications.deleted')]);

        return to_route('my-publications.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(): array
    {
        return [
            'categories' => CategoryResource::collection(Category::tree())->resolve(),
            'options' => [
                'modalities' => EnumOptions::from(PublicationModality::cases()),
                'conditions' => EnumOptions::from(ItemCondition::cases()),
            ],
        ];
    }
}
