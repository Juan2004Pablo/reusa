<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('deleting an account removes its publications and photo files', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Publication::factory()->ownedBy($user)->create();
    $trashed = Publication::factory()->ownedBy($user)->create();
    $trashed->delete();
    $theirs = Publication::factory()->ownedBy($other)->create();

    foreach ([$mine, $trashed, $theirs] as $publication) {
        Storage::disk('public')->put("publications/{$publication->id}/foto.png", 'x');
        $publication->images()->create(['path' => "publications/{$publication->id}/foto.png", 'position' => 0]);
    }

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');

    expect(User::find($user->id))->toBeNull()
        ->and(Publication::withTrashed()->where('user_id', $user->id)->count())->toBe(0);

    Storage::disk('public')->assertMissing("publications/{$mine->id}/foto.png");
    Storage::disk('public')->assertMissing("publications/{$trashed->id}/foto.png");
    Storage::disk('public')->assertExists("publications/{$theirs->id}/foto.png");
});
