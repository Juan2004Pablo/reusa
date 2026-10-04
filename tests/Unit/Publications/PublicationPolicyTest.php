<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;
use App\Policies\PublicationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new PublicationPolicy;
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->publication = Publication::factory()->ownedBy($this->owner)->create();
});

test('the policy is registered for the model', function () {
    expect(Gate::getPolicyFor(Publication::class))->toBeInstanceOf(PublicationPolicy::class);
});

test('the catalog and visible publications are public', function () {
    expect($this->policy->viewAny(null))->toBeTrue()
        ->and($this->policy->view(null, $this->publication))->toBeTrue()
        ->and($this->policy->view($this->other, $this->publication))->toBeTrue();
});

test('hidden publications are only viewable by the owner and admins', function () {
    $hidden = Publication::factory()->ownedBy($this->owner)->hidden()->create();

    expect($this->policy->view(null, $hidden))->toBeFalse()
        ->and($this->policy->view($this->other, $hidden))->toBeFalse()
        ->and($this->policy->view($this->owner, $hidden))->toBeTrue()
        ->and($this->policy->view($this->admin, $hidden))->toBeTrue();
});

test('active users can create, inactive users cannot', function () {
    expect($this->policy->create($this->other))->toBeTrue()
        ->and($this->policy->create(User::factory()->inactive()->create()))->toBeFalse();
});

test('only the owner can update, delete and change the status', function (string $ability) {
    expect($this->policy->{$ability}($this->owner, $this->publication))->toBeTrue()
        ->and($this->policy->{$ability}($this->other, $this->publication))->toBeFalse()
        ->and($this->policy->{$ability}($this->admin, $this->publication))->toBeFalse();
})->with(['update', 'delete', 'changeStatus']);

test('only admins can moderate', function () {
    expect($this->policy->moderate($this->admin, $this->publication))->toBeTrue()
        ->and($this->policy->moderate($this->owner, $this->publication))->toBeFalse()
        ->and($this->policy->moderate($this->other, $this->publication))->toBeFalse();
});

test('an admin who owns a publication can manage it like anyone else', function () {
    $own = Publication::factory()->ownedBy($this->admin)->create();

    expect($this->policy->update($this->admin, $own))->toBeTrue();
});
