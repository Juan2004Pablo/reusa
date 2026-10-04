<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\HidePublication;
use App\Actions\Admin\UnhidePublication;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HidePublicationRequest;
use App\Http\Resources\Admin\PublicationResource;
use App\Models\Publication;
use App\Models\User;
use App\Support\EnumOptions;
use App\Support\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    private const array VISIBILITY = ['all', 'visible', 'hidden'];

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $search = trim($request->string('q')->limit(100, '')->toString());
        $status = PublicationStatus::tryFrom($request->string('status')->toString());
        $visibility = in_array($request->string('visibility')->toString(), self::VISIBILITY, true)
            ? $request->string('visibility')->toString()
            : 'all';

        $publications = Publication::query()
            ->with(['category', 'images', 'user', 'hiddenBy'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = LikePattern::contains($search);
                $escape = LikePattern::ESCAPE;
                $query->where(fn (Builder $q) => $q
                    ->whereRaw("LOWER(title) LIKE ? ESCAPE '{$escape}'", [$pattern])
                    ->orWhereHas('user', fn (Builder $u) => $u
                        ->whereRaw("LOWER(name) LIKE ? ESCAPE '{$escape}'", [$pattern])
                        ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '{$escape}'", [$pattern])));
            })
            ->when($status, fn (Builder $query, PublicationStatus $status) => $query->where('status', $status))
            ->when($visibility === 'hidden', fn (Builder $query) => $query->whereNotNull('hidden_at'))
            ->when($visibility === 'visible', fn (Builder $query) => $query->whereNull('hidden_at'))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/publications', [
            'publications' => PublicationResource::collection($publications),
            'filters' => ['q' => $search, 'status' => $status->value ?? '', 'visibility' => $visibility],
            'statuses' => EnumOptions::from(PublicationStatus::cases()),
        ]);
    }

    public function hide(HidePublicationRequest $request, Publication $publication, HidePublication $action): RedirectResponse
    {
        $action->execute($publication, $request->user(), $request->input('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('messages.admin.publication_hidden')]);

        return back();
    }

    public function unhide(Request $request, Publication $publication, UnhidePublication $action): RedirectResponse
    {
        Gate::authorize('moderate', $publication);

        $action->execute($publication);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('messages.admin.publication_unhidden')]);

        return back();
    }
}
