import { Head, Link, router } from '@inertiajs/react';
import { PackageSearch, Search, SlidersHorizontal, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import EmptyState from '@/components/empty-state';
import PaginationNav from '@/components/pagination-nav';
import CatalogFiltersPanel from '@/components/publications/catalog-filters';
import PublicationCard, {
    PublicationCardSkeleton,
} from '@/components/publications/publication-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useNavigating } from '@/hooks/use-navigating';
import {
    activeFilterCount,
    catalogQuery,
    DEFAULT_FILTERS,
} from '@/lib/catalog';
import { pluralize } from '@/lib/format';
import { create, index } from '@/routes/publications';
import type {
    CatalogFilters,
    Category,
    Option,
    Paginated,
    PublicationCard as PublicationCardType,
} from '@/types';

type Props = {
    publications: Paginated<PublicationCardType>;
    filters: CatalogFilters;
    categories: Category[];
    options: { modalities: Option[]; statuses: Option[]; sorts: Option[] };
};

export default function PublicationsIndex({
    publications,
    filters,
    categories,
    options,
}: Props) {
    const navigating = useNavigating();
    const [search, setSearch] = useState(filters.q);
    const [sheetOpen, setSheetOpen] = useState(false);
    const latestFilters = useRef(filters);

    useEffect(() => {
        latestFilters.current = filters;
    }, [filters]);

    const visit = (next: CatalogFilters) => {
        router.get(index.url(), catalogQuery(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    // La búsqueda por texto espera a que la persona deje de escribir.
    useEffect(() => {
        const text = search.trim();

        if (text === latestFilters.current.q) {
            return;
        }

        const timer = setTimeout(
            () => visit({ ...latestFilters.current, q: text }),
            450,
        );

        return () => clearTimeout(timer);
    }, [search]);

    // Si los filtros cambian desde fuera (p. ej. "Limpiar filtros"), se sincroniza el campo.
    useEffect(() => {
        setSearch(filters.q);
    }, [filters.q]);

    const change = (patch: Partial<CatalogFilters>) =>
        visit({ ...filters, ...patch });

    const reset = () => {
        setSearch('');
        visit({ ...DEFAULT_FILTERS, sort: filters.sort });
    };

    const submitSearch = (event: React.FormEvent) => {
        event.preventDefault();
        change({ q: search.trim() });
    };

    const activeCount = activeFilterCount(filters);
    const macro = categories.find(
        (c) =>
            c.slug === filters.category ||
            c.children?.some((child) => child.slug === filters.subcategory),
    );
    const sub = macro?.children?.find((c) => c.slug === filters.subcategory);
    const modality = options.modalities.find(
        (o) => o.value === filters.modality,
    );
    const status = options.statuses.find((o) => o.value === filters.status);

    const chips: { key: string; label: string; clear: () => void }[] = [];

    if (filters.q) {
        chips.push({
            key: 'q',
            label: `“${filters.q}”`,
            clear: () => change({ q: '' }),
        });
    }

    if (macro) {
        chips.push({
            key: 'category',
            label: macro.name,
            clear: () => change({ category: '', subcategory: '' }),
        });
    }

    if (sub) {
        chips.push({
            key: 'subcategory',
            label: sub.name,
            clear: () => change({ subcategory: '' }),
        });
    }

    if (modality) {
        chips.push({
            key: 'modality',
            label: modality.label,
            clear: () => change({ modality: '' }),
        });
    }

    if (filters.status !== DEFAULT_FILTERS.status) {
        chips.push({
            key: 'status',
            label:
                filters.status === 'all'
                    ? 'Todos los estados'
                    : (status?.label ?? filters.status),
            clear: () => change({ status: DEFAULT_FILTERS.status }),
        });
    }

    const panel = (idPrefix: string) => (
        <CatalogFiltersPanel
            idPrefix={idPrefix}
            filters={filters}
            categories={categories}
            modalities={options.modalities}
            statuses={options.statuses}
            onChange={change}
            onReset={reset}
        />
    );

    return (
        <>
            <Head title="Catálogo de objetos" />

            <div className="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6">
                <header className="mb-6 space-y-4">
                    <div>
                        <h1 className="text-3xl font-semibold tracking-tight">
                            Catálogo
                        </h1>
                        <p className="text-muted-foreground">
                            Encuentra objetos para donar, intercambiar o comprar
                            entre vecinos.
                        </p>
                    </div>

                    <form
                        role="search"
                        onSubmit={submitSearch}
                        className="flex gap-2"
                    >
                        <div className="relative flex-1">
                            <Search
                                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden
                            />
                            <Input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por título o descripción…"
                                aria-label="Buscar objetos"
                                className="h-11 pl-9"
                                maxLength={100}
                            />
                        </div>
                        <Button type="submit" size="lg" className="h-11">
                            Buscar
                        </Button>
                    </form>
                </header>

                <div className="grid gap-8 lg:grid-cols-[17rem_1fr]">
                    <aside
                        className="hidden lg:block"
                        aria-label="Filtros del catálogo"
                    >
                        <div className="sticky top-24 rounded-xl border bg-card p-5">
                            <h2 className="mb-4 font-semibold">Filtros</h2>
                            {panel('desktop')}
                        </div>
                    </aside>

                    <section aria-label="Resultados" className="min-w-0">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <p
                                className="text-sm text-muted-foreground"
                                role="status"
                                aria-live="polite"
                            >
                                {navigating
                                    ? 'Buscando…'
                                    : pluralize(
                                          publications.meta.total,
                                          'objeto',
                                          'objetos',
                                      )}
                            </p>

                            <div className="flex items-center gap-2">
                                <Sheet
                                    open={sheetOpen}
                                    onOpenChange={setSheetOpen}
                                >
                                    <SheetTrigger asChild>
                                        <Button
                                            variant="outline"
                                            className="lg:hidden"
                                        >
                                            <SlidersHorizontal aria-hidden />
                                            Filtros
                                            {activeCount > 0 && (
                                                <Badge className="ml-1 rounded-full px-1.5">
                                                    {activeCount}
                                                </Badge>
                                            )}
                                        </Button>
                                    </SheetTrigger>
                                    <SheetContent
                                        side="left"
                                        className="w-[88%] max-w-sm overflow-y-auto"
                                    >
                                        <SheetHeader>
                                            <SheetTitle>Filtros</SheetTitle>
                                            <SheetDescription>
                                                Los resultados se actualizan al
                                                elegir cada filtro.
                                            </SheetDescription>
                                        </SheetHeader>
                                        <div className="px-4 pb-6">
                                            {panel('mobile')}
                                            <Button
                                                className="mt-6 w-full"
                                                onClick={() =>
                                                    setSheetOpen(false)
                                                }
                                            >
                                                Ver{' '}
                                                {pluralize(
                                                    publications.meta.total,
                                                    'resultado',
                                                    'resultados',
                                                )}
                                            </Button>
                                        </div>
                                    </SheetContent>
                                </Sheet>

                                <label htmlFor="sort" className="sr-only">
                                    Ordenar por
                                </label>
                                <Select
                                    value={filters.sort}
                                    onValueChange={(value) =>
                                        change({ sort: value })
                                    }
                                >
                                    <SelectTrigger
                                        id="sort"
                                        className="w-[13.5rem]"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent align="end">
                                        {options.sorts.map((sort) => (
                                            <SelectItem
                                                key={sort.value}
                                                value={sort.value}
                                            >
                                                {sort.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {chips.length > 0 && (
                            <ul
                                className="mb-4 flex flex-wrap gap-2"
                                aria-label="Filtros activos"
                            >
                                {chips.map((chip) => (
                                    <li key={chip.key}>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            className="h-8 rounded-full"
                                            onClick={chip.clear}
                                            aria-label={`Quitar filtro ${chip.label}`}
                                        >
                                            {chip.label}
                                            <X aria-hidden />
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {navigating ? (
                            <div
                                className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
                                aria-busy="true"
                            >
                                {Array.from({ length: 6 }, (_, i) => (
                                    <PublicationCardSkeleton key={i} />
                                ))}
                            </div>
                        ) : publications.data.length === 0 ? (
                            <EmptyState
                                icon={PackageSearch}
                                title="No encontramos objetos con esos filtros"
                                description={
                                    activeCount > 0
                                        ? 'Prueba con otras palabras o quita algún filtro. También puedes publicar lo que buscas ofrecer.'
                                        : 'Todavía no hay objetos publicados. ¡Sé la primera persona en compartir algo!'
                                }
                            >
                                {activeCount > 0 && (
                                    <Button variant="outline" onClick={reset}>
                                        Limpiar filtros
                                    </Button>
                                )}
                                <Button asChild>
                                    <Link href={create()}>
                                        Publicar un objeto
                                    </Link>
                                </Button>
                            </EmptyState>
                        ) : (
                            <ul className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                {publications.data.map((publication) => (
                                    <li key={publication.id} className="flex">
                                        <div className="w-full">
                                            <PublicationCard
                                                publication={publication}
                                            />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <PaginationNav
                            className="mt-8"
                            currentPage={publications.meta.current_page}
                            lastPage={publications.meta.last_page}
                            href={(page) =>
                                index({
                                    query: catalogQuery(filters, page),
                                })
                            }
                        />
                    </section>
                </div>
            </div>
        </>
    );
}
