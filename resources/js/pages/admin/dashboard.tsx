import { Head, Link } from '@inertiajs/react';
import { EyeOff, Package, UserCheck, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import AdminNav from '@/components/admin/admin-nav';
import Heading from '@/components/heading';
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

const barColor: Record<string, string> = {
    donation: 'bg-donation-foreground',
    exchange: 'bg-exchange-foreground',
    sale: 'bg-sale-foreground',
};

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Administración" />

            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Administración"
                    description="Resumen de la actividad de ReUsa y herramientas de moderación."
                />
                <AdminNav />

                <dl className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard
                        icon={Users}
                        label="Usuarios"
                        value={stats.users.total}
                        hint={pluralize(
                            stats.users.admins,
                            'administrador',
                            'administradores',
                        )}
                        href={usersIndex()}
                    />
                    <StatCard
                        icon={UserCheck}
                        label="Cuentas activas"
                        value={stats.users.active}
                        hint={pluralize(
                            stats.users.inactive,
                            'desactivada',
                            'desactivadas',
                        )}
                        href={usersIndex()}
                    />
                    <StatCard
                        icon={Package}
                        label="Publicaciones"
                        value={stats.publications.total}
                        hint="Sin contar las eliminadas"
                        href={publicationsIndex()}
                    />
                    <StatCard
                        icon={EyeOff}
                        label="Ocultas"
                        value={stats.publications.hidden}
                        hint="Retiradas del catálogo"
                        href={publicationsIndex({
                            query: { visibility: 'hidden' },
                        })}
                    />
                </dl>

                <div className="mt-6 grid gap-6 md:grid-cols-2">
                    <BreakdownCard
                        title="Publicaciones por estado"
                        items={stats.publications.by_status}
                        total={stats.publications.total}
                        color={() => 'bg-primary'}
                    />
                    <BreakdownCard
                        title="Publicaciones por modalidad"
                        items={stats.publications.by_modality}
                        total={stats.publications.total}
                        color={(value) => barColor[value] ?? 'bg-primary'}
                    />
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Administración', href: dashboard() }],
};

function StatCard({
    icon: Icon,
    label,
    value,
    hint,
    href,
}: {
    icon: LucideIcon;
    label: string;
    value: number;
    hint: string;
    href: ReturnType<typeof usersIndex>;
}) {
    return (
        <Link
            href={href}
            className="rounded-xl border bg-card p-4 transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
        >
            <div className="mb-3 flex items-center justify-between">
                <dt className="text-sm text-muted-foreground">{label}</dt>
                <Icon className="size-4 text-primary" aria-hidden />
            </div>
            <dd className="text-3xl font-semibold tabular-nums">
                {formatInteger(value)}
            </dd>
            <p className="mt-1 text-xs text-muted-foreground">{hint}</p>
        </Link>
    );
}

function BreakdownCard({
    title,
    items,
    total,
    color,
}: {
    title: string;
    items: Breakdown;
    total: number;
    color: (value: string) => string;
}) {
    return (
        <section className="rounded-xl border bg-card p-5">
            <h2 className="mb-4 font-semibold">{title}</h2>
            <ul className="space-y-3">
                {items.map((item) => {
                    const percent =
                        total > 0 ? Math.round((item.total / total) * 100) : 0;

                    return (
                        <li key={item.value}>
                            <div className="mb-1 flex justify-between text-sm">
                                <span>{item.label}</span>
                                <span className="text-muted-foreground tabular-nums">
                                    {item.total} · {percent}%
                                </span>
                            </div>
                            <div
                                className="h-2 overflow-hidden rounded-full bg-muted"
                                role="img"
                                aria-label={`${item.label}: ${item.total} de ${total}`}
                            >
                                <div
                                    className={cn(
                                        'h-full rounded-full',
                                        color(item.value),
                                    )}
                                    style={{ width: `${percent}%` }}
                                />
                            </div>
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}
