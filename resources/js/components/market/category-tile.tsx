import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import CategoryIcon from '@/components/publications/category-icon';
import { cn } from '@/lib/utils';
import { index } from '@/routes/publications';
import type { Category } from '@/types';

/**
 * Casilla de una macrocategoría para la portada: la foto del objeto más reciente a todo color,
 * la cifra de objetos disponibles como protagonista y el nombre sobre un degradado neutro que
 * solo cubre la zona del texto. Sin foto (categoría vacía) queda un hueco punteado con su icono,
 * como un estante vacío. `featured` la hace más grande.
 */
export default function CategoryTile({
    category,
    featured = false,
    className,
}: {
    category: Category & { available_count: number; cover_url: string | null };
    featured?: boolean;
    className?: string;
}) {
    const count = category.available_count;
    const hasCover = category.cover_url !== null;

    return (
        <Link
            href={index({ query: { category: category.slug } })}
            className={cn(
                'group relative isolate flex flex-col justify-between gap-6 overflow-hidden rounded-md p-5 transition duration-300 ease-out hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-black/30 focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none motion-reduce:transform-none motion-reduce:transition-none sm:p-6',
                hasCover
                    ? 'bg-foreground/10 text-white'
                    : 'border-2 border-dashed border-foreground/25 text-foreground hover:border-solid hover:border-foreground/60 hover:bg-foreground/5',
                featured ? 'min-h-72 sm:min-h-80' : 'min-h-56',
                className,
            )}
        >
            {hasCover ? (
                <>
                    <img
                        src={category.cover_url ?? undefined}
                        alt=""
                        loading="lazy"
                        decoding="async"
                        className="absolute inset-0 -z-20 size-full object-cover transition duration-700 ease-out group-hover:scale-110 motion-reduce:transform-none motion-reduce:transition-none"
                    />
                    <span
                        aria-hidden
                        className="absolute inset-0 -z-10 bg-linear-to-t from-black/85 from-20% via-black/45 via-60% to-transparent transition-opacity duration-500 group-hover:opacity-80 motion-reduce:transition-none"
                    />
                </>
            ) : (
                <CategoryIcon
                    name={category.icon}
                    className={cn(
                        'shrink-0 text-muted-foreground',
                        featured ? 'size-14' : 'size-10',
                    )}
                />
            )}

            <span className="mt-auto flex items-end justify-between gap-4">
                <span className="min-w-0 space-y-2">
                    <span
                        className={cn(
                            'block font-mono leading-none font-semibold tabular-nums',
                            featured ? 'text-7xl' : 'text-5xl',
                            count === 0 && 'text-muted-foreground',
                        )}
                    >
                        {count}
                    </span>
                    <span
                        className={cn(
                            'block leading-snug font-semibold',
                            featured && 'text-xl',
                        )}
                    >
                        {category.name}
                    </span>
                    <span
                        className={cn(
                            'type-label block',
                            hasCover
                                ? 'text-white/80'
                                : 'text-muted-foreground',
                        )}
                    >
                        {count === 0
                            ? 'Aún sin objetos'
                            : count === 1
                              ? 'objeto disponible'
                              : 'objetos disponibles'}
                    </span>
                </span>
                <span
                    aria-hidden
                    className={cn(
                        'grid size-11 shrink-0 place-items-center rounded-full opacity-70 transition duration-300 group-hover:scale-110 group-hover:opacity-100 group-focus-visible:scale-110 group-focus-visible:opacity-100 motion-reduce:transition-none',
                        hasCover
                            ? 'group-hover:bg-white group-hover:text-black group-focus-visible:bg-white group-focus-visible:text-black'
                            : 'group-hover:bg-foreground group-hover:text-background group-focus-visible:bg-foreground group-focus-visible:text-background',
                    )}
                >
                    <ArrowUpRight className="size-6 transition-transform duration-300 group-hover:rotate-45 group-focus-visible:rotate-45 motion-reduce:transition-none" />
                </span>
            </span>
        </Link>
    );
}
