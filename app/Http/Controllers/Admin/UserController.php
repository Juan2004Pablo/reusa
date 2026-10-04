<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeUserRole;
use App\Actions\Admin\SetUserActiveState;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Support\EnumOptions;
use App\Support\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $search = trim($request->string('q')->limit(100, '')->toString());

        $users = User::query()
            ->withCount('publications')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = LikePattern::contains($search);
                $query->where(fn (Builder $q) => $q
                    ->whereRaw("LOWER(name) LIKE ? ESCAPE '".LikePattern::ESCAPE."'", [$pattern])
                    ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '".LikePattern::ESCAPE."'", [$pattern]));
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/users', [
            'users' => UserResource::collection($users),
            'filters' => ['q' => $search],
            'roles' => EnumOptions::from(UserRole::cases()),
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user, SetUserActiveState $action): RedirectResponse
    {
        $action->execute($user, $request->boolean('is_active'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __($user->is_active ? 'messages.admin.user_activated' : 'messages.admin.user_deactivated', ['name' => $user->name]),
        ]);

        return back();
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user, ChangeUserRole $action): RedirectResponse
    {
        $action->execute($user, $request->role());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('messages.admin.role_changed', ['name' => $user->name, 'role' => mb_strtolower($user->role->label())]),
        ]);

        return back();
    }
}
