<?php

declare(strict_types=1);

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;
use App\Support\EnumOptions;

test('modalities are backed by their database values and have Spanish labels', function () {
    expect(PublicationModality::from('donation'))->toBe(PublicationModality::Donation)
        ->and(PublicationModality::Donation->label())->toBe('Donación')
        ->and(PublicationModality::Exchange->label())->toBe('Intercambio')
        ->and(PublicationModality::Sale->label())->toBe('Venta')
        ->and(PublicationModality::tryFrom('barter'))->toBeNull();
});

test('only a sale requires a price', function () {
    expect(PublicationModality::Sale->requiresPrice())->toBeTrue()
        ->and(PublicationModality::Donation->requiresPrice())->toBeFalse()
        ->and(PublicationModality::Exchange->requiresPrice())->toBeFalse();
});

test('only an exchange accepts what the owner wants in return', function () {
    expect(PublicationModality::Exchange->acceptsWantedInExchange())->toBeTrue()
        ->and(PublicationModality::Donation->acceptsWantedInExchange())->toBeFalse()
        ->and(PublicationModality::Sale->acceptsWantedInExchange())->toBeFalse();
});

test('item conditions have Spanish labels', function () {
    expect(ItemCondition::LikeNew->value)->toBe('like_new')
        ->and(ItemCondition::LikeNew->label())->toBe('Como nuevo')
        ->and(ItemCondition::Good->label())->toBe('Buen estado')
        ->and(ItemCondition::Fair->label())->toBe('Aceptable');
});

test('statuses have Spanish labels', function () {
    expect(PublicationStatus::Available->label())->toBe('Disponible')
        ->and(PublicationStatus::Reserved->label())->toBe('Reservado')
        ->and(PublicationStatus::Delivered->label())->toBe('Entregado')
        ->and(PublicationStatus::Sold->label())->toBe('Vendido');
});

test('delivered and sold are final statuses', function () {
    expect(PublicationStatus::Delivered->isFinal())->toBeTrue()
        ->and(PublicationStatus::Sold->isFinal())->toBeTrue()
        ->and(PublicationStatus::Available->isFinal())->toBeFalse()
        ->and(PublicationStatus::Reserved->isFinal())->toBeFalse();
});

test('a sale closes as sold and the other modalities close as delivered', function () {
    expect(PublicationStatus::closedFor(PublicationModality::Sale))->toBe(PublicationStatus::Sold)
        ->and(PublicationStatus::closedFor(PublicationModality::Donation))->toBe(PublicationStatus::Delivered)
        ->and(PublicationStatus::closedFor(PublicationModality::Exchange))->toBe(PublicationStatus::Delivered);
});

/*
 * Tabla de verdad completa, escrita a mano (no derivada de la implementación):
 * para cada modalidad, los únicos cambios válidos desde cada estado.
 */
test('allowed transitions match the business rules', function (string $modality, string $from, array $allowed) {
    $actual = array_map(
        fn (PublicationStatus $status) => $status->value,
        PublicationStatus::from($from)->allowedTransitions(PublicationModality::from($modality)),
    );

    expect($actual)->toEqualCanonicalizing($allowed);
})->with([
    ['donation', 'available', ['reserved', 'delivered']],
    ['donation', 'reserved', ['available', 'delivered']],
    ['donation', 'delivered', []],
    ['donation', 'sold', []],
    ['exchange', 'available', ['reserved', 'delivered']],
    ['exchange', 'reserved', ['available', 'delivered']],
    ['exchange', 'delivered', []],
    ['exchange', 'sold', []],
    ['sale', 'available', ['reserved', 'sold']],
    ['sale', 'reserved', ['available', 'sold']],
    ['sale', 'delivered', []],
    ['sale', 'sold', []],
]);

test('canTransitionTo agrees with the table for every combination', function () {
    $expected = [
        'donation' => ['available' => ['reserved', 'delivered'], 'reserved' => ['available', 'delivered'], 'delivered' => [], 'sold' => []],
        'exchange' => ['available' => ['reserved', 'delivered'], 'reserved' => ['available', 'delivered'], 'delivered' => [], 'sold' => []],
        'sale' => ['available' => ['reserved', 'sold'], 'reserved' => ['available', 'sold'], 'delivered' => [], 'sold' => []],
    ];

    foreach ($expected as $modality => $byStatus) {
        foreach ($byStatus as $from => $allowed) {
            foreach (PublicationStatus::cases() as $to) {
                expect(PublicationStatus::from($from)->canTransitionTo($to, PublicationModality::from($modality)))
                    ->toBe(in_array($to->value, $allowed, true), "{$modality}: {$from} → {$to->value}");
            }
        }
    }
});

test('a status can never transition to itself', function () {
    foreach (PublicationModality::cases() as $modality) {
        foreach (PublicationStatus::cases() as $status) {
            expect($status->canTransitionTo($status, $modality))->toBeFalse();
        }
    }
});

test('sort options have Spanish labels', function () {
    expect(PublicationSort::Recent->label())->toBe('Más recientes')
        ->and(PublicationSort::PriceAsc->label())->toBe('Precio: menor a mayor')
        ->and(PublicationSort::PriceDesc->label())->toBe('Precio: mayor a menor');
});

test('enum options are converted into value/label pairs', function () {
    expect(EnumOptions::from(PublicationModality::cases()))->toBe([
        ['value' => 'donation', 'label' => 'Donación'],
        ['value' => 'exchange', 'label' => 'Intercambio'],
        ['value' => 'sale', 'label' => 'Venta'],
    ])->and(EnumOptions::from([]))->toBe([]);
});
