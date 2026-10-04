import { RotateCcw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { activeFilterCount } from '@/lib/catalog';
import { cn } from '@/lib/utils';
import type { CatalogFilters, Category, Option } from '@/types';

const ALL = '__all__';

type Props = {
    filters: CatalogFilters;
    categories: Category[];
    modalities: Option[];
    statuses: Option[];
    onChange: (patch: Partial<CatalogFilters>) => void;
    onReset: () => void;
    /** Evita ids duplicados cuando el panel se renderiza dos veces (escritorio y móvil). */
    idPrefix: string;
};

/** Macrocategoría efectiva: la elegida o la del padre de la subcategoría seleccionada. */
function resolveMacro(categories: Category[], filters: CatalogFilters) {
    return (
        categories.find((c) => c.slug === filters.category) ??
        categories.find((c) =>
            c.children?.some((child) => child.slug === filters.subcategory),
        )
    );
}

export default function CatalogFiltersPanel({
    filters,
    categories,
    modalities,
    statuses,
    onChange,
    onReset,
    idPrefix,
}: Props) {
    const macro = resolveMacro(categories, filters);
    const count = activeFilterCount(filters);

    return (
        <div className="divide-y divide-border [&>*]:py-5 [&>*:first-child]:pt-0">
            <div className="space-y-2">
                <label
                    htmlFor={`${idPrefix}-category`}
                    className="type-label text-muted-foreground"
                >
                    Categoría
                </label>
                <Select
                    value={macro?.slug ?? ALL}
                    onValueChange={(value) =>
                        onChange({
                            category: value === ALL ? '' : value,
                            subcategory: '',
                        })
                    }
                >
                    <SelectTrigger
                        id={`${idPrefix}-category`}
                        className="w-full"
                    >
                        <SelectValue placeholder="Todas las categorías" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ALL}>
                            Todas las categorías
                        </SelectItem>
                        {categories.map((category) => (
                            <SelectItem key={category.id} value={category.slug}>
                                {category.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="space-y-2">
                <label
                    htmlFor={`${idPrefix}-subcategory`}
                    className="type-label text-muted-foreground"
                >
                    Subcategoría
                </label>
                <Select
                    value={filters.subcategory || ALL}
                    disabled={!macro}
                    onValueChange={(value) =>
                        onChange({
                            category: macro?.slug ?? '',
                            subcategory: value === ALL ? '' : value,
                        })
                    }
                >
                    <SelectTrigger
                        id={`${idPrefix}-subcategory`}
                        className="w-full"
                    >
                        <SelectValue placeholder="Todas las subcategorías" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ALL}>
                            Todas las subcategorías
                        </SelectItem>
                        {macro?.children?.map((child) => (
                            <SelectItem key={child.id} value={child.slug}>
                                {child.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {!macro && (
                    <p className="text-xs text-muted-foreground">
                        Elige primero una categoría.
                    </p>
                )}
            </div>

            <div>
                <fieldset className="space-y-1">
                    <legend className="type-label mb-2 block text-muted-foreground">
                        Modalidad
                    </legend>
                    {[{ value: '', label: 'Todas' }, ...modalities].map(
                        (option) => {
                            const id = `${idPrefix}-modality-${option.value || 'all'}`;

                            return (
                                <label
                                    key={option.value}
                                    htmlFor={id}
                                    className="-mx-2 flex cursor-pointer items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm hover:bg-accent has-[:checked]:font-semibold has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-ring/50"
                                >
                                    <input
                                        id={id}
                                        type="radio"
                                        name={`${idPrefix}-modality`}
                                        value={option.value}
                                        checked={
                                            filters.modality === option.value
                                        }
                                        onChange={() =>
                                            onChange({ modality: option.value })
                                        }
                                        className="size-4 accent-[var(--primary)]"
                                    />
                                    {option.value && (
                                        <span
                                            className={cn(
                                                'size-2 rounded-full',
                                                option.value === 'donation' &&
                                                    'bg-donation',
                                                option.value === 'exchange' &&
                                                    'bg-exchange',
                                                option.value === 'sale' &&
                                                    'bg-sale',
                                            )}
                                            aria-hidden
                                        />
                                    )}
                                    {option.label}
                                </label>
                            );
                        },
                    )}
                </fieldset>
            </div>

            <div className="space-y-2">
                <label
                    htmlFor={`${idPrefix}-status`}
                    className="type-label text-muted-foreground"
                >
                    Estado
                </label>
                <Select
                    value={filters.status}
                    onValueChange={(value) => onChange({ status: value })}
                >
                    <SelectTrigger id={`${idPrefix}-status`} className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {statuses.map((status) => (
                            <SelectItem key={status.value} value={status.value}>
                                {status.label}
                            </SelectItem>
                        ))}
                        <SelectItem value="all">Todos los estados</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div>
                <Button
                    type="button"
                    variant="ghost"
                    className="-mx-3 text-muted-foreground"
                    onClick={onReset}
                    disabled={count === 0}
                >
                    <RotateCcw aria-hidden />
                    Limpiar filtros
                </Button>
            </div>
        </div>
    );
}
