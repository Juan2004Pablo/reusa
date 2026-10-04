<?php

declare(strict_types=1);

use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;
use App\Support\PublicationFilters;

test('the defaults show available publications, most recent first', function () {
    $filters = PublicationFilters::fromArray([]);

    expect($filters->status)->toBe(PublicationStatus::Available)
        ->and($filters->sort)->toBe(PublicationSort::Recent)
        ->and($filters->search)->toBeNull()
        ->and($filters->modality)->toBeNull();
});

test('all filters are parsed', function () {
    $filters = PublicationFilters::fromArray([
        'q' => '  mesa  ',
        'category' => 'muebles',
        'subcategory' => 'escritorios',
        'modality' => 'sale',
        'status' => 'reserved',
        'sort' => 'price_desc',
    ]);

    expect($filters->search)->toBe('mesa')
        ->and($filters->category)->toBe('muebles')
        ->and($filters->subcategory)->toBe('escritorios')
        ->and($filters->modality)->toBe(PublicationModality::Sale)
        ->and($filters->status)->toBe(PublicationStatus::Reserved)
        ->and($filters->sort)->toBe(PublicationSort::PriceDesc);
});

test('status=all removes the status restriction', function () {
    expect(PublicationFilters::fromArray(['status' => 'all'])->status)->toBeNull();
});

test('invalid values fall back to the defaults', function () {
    $filters = PublicationFilters::fromArray(['modality' => 'x', 'status' => 'x', 'sort' => 'x', 'q' => '   ', 'category' => ['a']]);

    expect($filters->modality)->toBeNull()
        ->and($filters->status)->toBe(PublicationStatus::Available)
        ->and($filters->sort)->toBe(PublicationSort::Recent)
        ->and($filters->search)->toBeNull()
        ->and($filters->category)->toBeNull();
});

test('search terms are lowercased words limited to five', function () {
    $filters = PublicationFilters::fromArray(['q' => 'Una MESA   de madera para el comedor grande']);

    expect($filters->searchTerms())->toBe(['una', 'mesa', 'de', 'madera', 'para']);
});

test('the array form mirrors the query string', function () {
    expect(PublicationFilters::fromArray(['q' => 'bici', 'status' => 'all'])->toArray())->toBe([
        'q' => 'bici',
        'category' => '',
        'subcategory' => '',
        'modality' => '',
        'status' => 'all',
        'sort' => 'recent',
    ]);
});
