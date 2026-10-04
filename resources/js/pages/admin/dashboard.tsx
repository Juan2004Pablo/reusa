import { Head, Link } from '@inertiajs/react';
import AdminNav from '@/components/admin/admin-nav';
import Heading from '@/components/heading';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatInteger, pluralize } from '@/lib/format';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import { index as publicationsIndex } from '@/routes/admin/publications';
import { index as usersIndex } from '@/routes/admin/users';

type Breakdown = { value: string; label: string; total: number }[];

type Props = {
    stats: {
        users: {
            total: number;
            active: number;
            inactive: number;
            admins: number;
        };
        publications: {
            total: number;
            hidden: number;
            by_status: Breakdown;
            by_modality: Breakdown;
        };
    };
};

/** Color de la marca de cada modalidad (validado con el verificador de paletas). */
const modalityFill: Record<string, string> = {
    donation: 'bg-donation',
    exchange: 'bg-exchange',
    sale: 'bg-sale',
};

export default function AdminDashboard({ stats }: Props) {
    const figures = [
        {
            label: 'Usuarios',
            value: stats.users.total,
            hint: pluralize(
                stats.users.admins,
                'administrador',
                'administradores',
            ),
            href: usersIndex(),
        },
        {
            label: 'Cuentas activas',
            value: stats.users.active,
            hint: pluralize(
                stats.users.inactive,
                'desactivada',
                'desactivadas',
            ),
            href: usersIndex(),
        },
        {
            label: 'Publicaciones',
            value: stats.publications.total,
            hint: 'Sin contar las eliminadas',
            href: publicationsIndex(),
        },
        {
            label: 'Ocultas',
            value: stats.publications.hidden,
            hint: 'Retiradas del catálogo',
            href: publicationsIndex({ query: { visibility: 'hidden' } }),
        },
    ];

    return (
        <>
            <Head title="Administración" />

            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Administración"
                    description="Resumen de la actividad de ReUsa y herramientas de moderación."
                />
                <AdminNav />

                <ul className="grid grid-cols-2 divide-x divide-y border-y lg:grid-cols-4 lg:divide-y-0">
                    {figures.map((figure) => (
                        <li key={figure.label}>
                            <Link
                                href={figure.href}
                                className="block space-y-1 px-5 py-5 first:pl-0 hover:bg-accent/50 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                            >
                                <span className="type-label block text-muted-foreground">
                                    {figure.label}
                                </span>
                                <span className="block font-mono text-4xl font-semibold tabular-nums">
                                    {formatInteger(figure.value)}
                                </span>
                                <span className="block text-sm text-muted-foreground">
                                    {figure.hint}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>

                <div className="mt-10 grid gap-x-12 gap-y-10 md:grid-cols-2">
                    <BreakdownBars
                        title="Publicaciones por estado"
                        items={stats.publications.by_status}
                        total={stats.publications.total}
                        fill={() => 'bg-primary'}
                    />
                    <BreakdownBars
                        title="Publicaciones por modalidad"
                        items={stats.publications.by_modality}
                        total={stats.publications.total}
                        fill={(value) => modalityFill[value] ?? 'bg-primary'}
                    />
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Administración', href: dashboard() }],
};

/**
 * Barras horizontales sobre una sola escala (0 a la cantidad máxima). Extremo de datos
 * redondeado (4 px) y anclado a la base, rótulos directos en tinta de texto y un
 * tooltip por barra. Las cifras exactas están siempre visibles, así que no hace falta
 * una tabla aparte.
 */
function BreakdownBars({
    title,
    items,
    total,
    fill,
}: {
    title: string;
    items: Breakdown;
    total: number;
    fill: (value: string) => string;
}) {
    const max = Math.max(1, ...items.map((item) => item.total));

    return (
        <section aria-label={title}>
            <h2 className="type-label border-t border-foreground/80 pt-3">
                {title}
            </h2>
            <ul className="mt-4 space-y-3">
                {items.map((item) => {
                    const share =
                        total > 0 ? Math.round((item.total / total) * 100) : 0;

                    return (
                        <li
                            key={item.value}
                            className="grid grid-cols-[6.5rem_1fr_3.5rem] items-center gap-3 text-sm"
                        >
                            <span className="truncate">{item.label}</span>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <button
                                        type="button"
                                        className="relative block h-6 w-full rounded-sm focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                        aria-label={`${item.label}: ${item.total} de ${total} (${share} %)`}
                                    >
                                        <span
                                            className={cn(
                                                'absolute inset-y-0 left-0 rounded-r-[4px]',
                                                fill(item.value),
                                            )}
                                            style={{
                                                width: `${(item.total / max) * 100}%`,
                                                minWidth:
                                                    item.total > 0 ? '4px' : 0,
                                            }}
                                        />
                                    </button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    {item.label}: {item.total} de {total} (
                                    {share} %)
                                </TooltipContent>
                            </Tooltip>
                            <span className="text-right font-mono tabular-nums">
                                {item.total}
                            </span>
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}
