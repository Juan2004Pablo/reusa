<?php

declare(strict_types=1);

use App\Actions\Publications\PublicationData;
use App\Enums\ItemCondition;
use App\Enums\PublicationModality;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validatedPublication(array $overrides = []): array
{
    return array_merge([
        'title' => 'Mesa',
        'description' => 'Mesa de comedor para seis personas.',
        'category_id' => '7',
        'modality' => 'donation',
        'condition' => 'like_new',
        'price' => '99000',
        'wanted_in_exchange' => '  Una silla ',
        'location' => 'Laureles',
    ], $overrides);
}

test('a sale keeps its price and drops what is wanted in exchange', function () {
    $data = PublicationData::fromValidated(validatedPublication(['modality' => 'sale']));

    expect($data->modality)->toBe(PublicationModality::Sale)
        ->and($data->price)->toBe(99000)
        ->and($data->wantedInExchange)->toBeNull()
        ->and($data->categoryId)->toBe(7)
        ->and($data->condition)->toBe(ItemCondition::LikeNew);
});

test('an exchange keeps the trimmed wanted text and drops the price', function () {
    $data = PublicationData::fromValidated(validatedPublication(['modality' => 'exchange']));

    expect($data->price)->toBeNull()
        ->and($data->wantedInExchange)->toBe('Una silla');
});

test('a donation has neither price nor wanted text', function () {
    $data = PublicationData::fromValidated(validatedPublication());

    expect($data->price)->toBeNull()
        ->and($data->wantedInExchange)->toBeNull();
});

test('an empty wanted text becomes null', function (mixed $wanted) {
    $data = PublicationData::fromValidated(validatedPublication(['modality' => 'exchange', 'wanted_in_exchange' => $wanted]));

    expect($data->wantedInExchange)->toBeNull();
})->with(['', '   ', null]);

test('it converts into model attributes', function () {
    $attributes = PublicationData::fromValidated(validatedPublication(['modality' => 'sale']))->toAttributes();

    expect($attributes)->toMatchArray([
        'title' => 'Mesa',
        'category_id' => 7,
        'modality' => PublicationModality::Sale,
        'condition' => ItemCondition::LikeNew,
        'price' => 99000,
        'wanted_in_exchange' => null,
        'location' => 'Laureles',
    ]);
});
