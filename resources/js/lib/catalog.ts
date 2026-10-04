import type { CatalogFilters } from '@/types';

export const DEFAULT_FILTERS: CatalogFilters = {
    q: '',
    category: '',
    subcategory: '',
    modality: '',
    status: 'available',
    sort: 'recent',
};

/**
 * Query string del catálogo: solo incluye los valores que difieren de los predeterminados,
 * para mantener URLs limpias y compartibles.
 */
export function catalogQuery(
    filters: CatalogFilters,
    page?: number,
): Record<string, string> {
    const query: Record<string, string> = {};

    (Object.keys(DEFAULT_FILTERS) as (keyof CatalogFilters)[]).forEach(
        (key) => {
            const value = filters[key].trim();

            if (value !== '' && value !== DEFAULT_FILTERS[key]) {
                query[key] = value;
            }
        },
    );

    if (page && page > 1) {
        query.page = String(page);
    }

    return query;
}

/** Cantidad de filtros activos (sin contar el orden). */
export function activeFilterCount(filters: CatalogFilters): number {
    return (['q', 'category', 'subcategory', 'modality', 'status'] as const)
        .map(
            (key) =>
                filters[key] !== DEFAULT_FILTERS[key] && filters[key] !== '',
        )
        .filter(Boolean).length;
}
