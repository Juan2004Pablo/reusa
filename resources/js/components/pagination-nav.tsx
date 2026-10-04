import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

type Href = NonNullable<InertiaLinkProps['href']>;

/** Números de página a mostrar: 1 … 4 5 6 … 20 */
function pageWindow(current: number, last: number): (number | 'gap')[] {
    const pages = new Set<number>([1, last, current - 1, current, current + 1]);
    const sorted = [...pages]
        .filter((page) => page >= 1 && page <= last)
        .sort((a, b) => a - b);
    const result: (number | 'gap')[] = [];

    sorted.forEach((page, index) => {
        if (index > 0 && page - sorted[index - 1] > 1) {
            result.push('gap');
        }

        result.push(page);
    });

    return result;
}

const base =
    'inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none';

export default function PaginationNav({
    currentPage,
    lastPage,
    href,
    className,
}: {
    currentPage: number;
    lastPage: number;
    /** Construye el enlace de una página conservando los filtros actuales. */
    href: (page: number) => Href;
    className?: string;
}) {
    if (lastPage <= 1) {
        return null;
    }

    return (
        <nav
            aria-label="Paginación"
            className={cn('flex flex-wrap justify-center gap-1.5', className)}
        >
            {currentPage > 1 ? (
                <Link
                    href={href(currentPage - 1)}
                    className={cn(base, 'bg-background hover:bg-accent')}
                    aria-label="Página anterior"
                    preserveScroll={false}
                >
                    <ChevronLeft className="size-4" aria-hidden />
                    <span className="hidden sm:inline">Anterior</span>
                </Link>
            ) : null}

            {pageWindow(currentPage, lastPage).map((page, index) =>
                page === 'gap' ? (
                    <span
                        key={`gap-${index}`}
                        className="inline-flex h-9 items-center px-1 text-muted-foreground"
                        aria-hidden
                    >
                        …
                    </span>
                ) : (
                    <Link
                        key={page}
                        href={href(page)}
                        aria-label={`Página ${page}`}
                        aria-current={page === currentPage ? 'page' : undefined}
                        className={cn(
                            base,
                            page === currentPage
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'bg-background hover:bg-accent',
                        )}
                    >
                        {page}
                    </Link>
                ),
            )}

            {currentPage < lastPage ? (
                <Link
                    href={href(currentPage + 1)}
                    className={cn(base, 'bg-background hover:bg-accent')}
                    aria-label="Página siguiente"
                >
                    <span className="hidden sm:inline">Siguiente</span>
                    <ChevronRight className="size-4" aria-hidden />
                </Link>
            ) : null}
        </nav>
    );
}
