<?php

declare(strict_types=1);

use App\Actions\Admin\ChangeUserRole;
use App\Actions\Admin\HidePublication;
use App\Actions\Admin\SetUserActiveState;
use App\Actions\Admin\UnhidePublication;
use App\Enums\UserRole;
use App\Models\Publication;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Support\LikePattern;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('HidePublication marks the publication as hidden by the admin', function () {
    $admin = User::factory()->admin()->create();
    $publication = Publication::factory()->create();

    app(HidePublication::class)->execute($publication, $admin, 'Motivo');

    expect($publication->refresh()->isHidden())->toBeTrue()
        ->and($publication->hidden_reason)->toBe('Motivo')
        ->and($publication->hidden_by)->toBe($admin->id);
});

test('UnhidePublication clears every moderation field', function () {
    $publication = Publication::factory()->hidden('Motivo', User::factory()->admin()->create())->create();

    app(UnhidePublication::class)->execute($publication);

    expect($publication->refresh()->isHidden())->toBeFalse()
        ->and($publication->hidden_reason)->toBeNull()
        ->and($publication->hidden_by)->toBeNull();
});

test('SetUserActiveState toggles the account', function () {
    $user = User::factory()->create();

    app(SetUserActiveState::class)->execute($user, false);
    expect($user->refresh()->is_active)->toBeFalse();

    app(SetUserActiveState::class)->execute($user, true);
    expect($user->refresh()->is_active)->toBeTrue();
});

test('ChangeUserRole sets the role', function () {
    $user = User::factory()->create();

    app(ChangeUserRole::class)->execute($user, UserRole::Admin);

    expect($user->refresh()->role)->toBe(UserRole::Admin);
});

test('UserPolicy only lets admins manage other accounts', function () {
    $policy = new UserPolicy;
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->updateStatus($admin, $user))->toBeTrue()
        ->and($policy->updateStatus($admin, $other))->toBeTrue()
        ->and($policy->updateStatus($admin, $admin))->toBeFalse()
        ->and($policy->updateStatus($user, $admin))->toBeFalse()
        ->and($policy->updateRole($admin, $user))->toBeTrue()
        ->and($policy->updateRole($admin, $admin))->toBeFalse()
        ->and($policy->updateRole($user, $other))->toBeFalse();
});

test('LikePattern escapes wildcards and lowercases', function () {
    expect(LikePattern::contains('50%_Off!'))->toBe('%50!%!_off!!%')
        ->and(LikePattern::contains('Ñandú'))->toBe('%ñandú%');
});
