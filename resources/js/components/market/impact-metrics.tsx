import { Gift, HeartHandshake, Users } from 'lucide-react';
import { useId, useState } from 'react';
import type { ComponentType } from 'react';
import { formatInteger } from '@/lib/format';
import type { Modality } from '@/types';

export type HomeStats = {
    available: number;
    modalities: { value: Modality; label: string; total: number }[];
    rehomed: number;
    published: number;
    neighbors: number;
    publishers: number;
    trends: {
        published: number[];
        rehomed: number[];
        neighbors: number[];
    };
};

const WIDTH = 160;
const HEIGHT = 64;
const PAD = 5;

/**
 * Minigráfica de área con tooltip por día. El último punto es hoy; `unit` nombra lo que se cuenta.
 */
function Sparkline({
    data,
    unit,
    label,
}: {
    data: number[];
    unit: [string, string];
    label: string;
}) {
    const gradientId = useId();
    const [active, setActive] = useState<number | null>(null);

    const max = Math.max(...data, 1);
    const step = (WIDTH - PAD * 2) / Math.max(data.length - 1, 1);
    const points = data.map((value, i) => ({
        x: PAD + i * step,
        y: PAD + (1 - value / max) * (HEIGHT - PAD * 2),
    }));
    const line = points.map((p) => `${p.x},${p.y}`).join(' L');
    const area = `M${PAD},${HEIGHT} L${line} L${WIDTH - PAD},${HEIGHT} Z`;
    const hovered = active === null ? null : points[active];

    function locate(event: React.PointerEvent<SVGSVGElement>) {
        const box = event.currentTarget.getBoundingClientRect();
        const x = ((event.clientX - box.left) / box.width) * WIDTH;
        setActive(
            Math.min(
                data.length - 1,
                Math.max(0, Math.round((x - PAD) / step)),
            ),
        );
    }

    const daysAgo = active === null ? 0 : data.length - 1 - active;

    return (
        <div className="relative h-16 w-full max-w-40">
            <svg
                viewBox={`0 0 ${WIDTH} ${HEIGHT}`}
                role="img"
                aria-label={label}
                className="size-full touch-none overflow-visible text-primary"
                onPointerMove={locate}
                onPointerDown={locate}
                onPointerLeave={() => setActive(null)}
            >
                <defs>
                    <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
                        <stop
                            offset="0%"
                            stopColor="currentColor"
                            stopOpacity={0.3}
                        />
                        <stop
                            offset="100%"
                            stopColor="currentColor"
                            stopOpacity={0.04}
                        />
                    </linearGradient>
                </defs>
                <path d={area} fill={`url(#${gradientId})`} />
                <path
                    d={`M${line}`}
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={2}
                    strokeLinejoin="round"
                    strokeLinecap="round"
                    vectorEffect="non-scaling-stroke"
                />
                {hovered && (
                    <>
                        <line
                            x1={hovered.x}
                            x2={hovered.x}
                            y1={0}
                            y2={HEIGHT}
                            stroke="currentColor"
                            strokeWidth={1}
                            strokeDasharray="2 2"
                            vectorEffect="non-scaling-stroke"
                        />
                        <circle
                            cx={hovered.x}
                            cy={hovered.y}
                            r={4}
                            fill="currentColor"
                            className="stroke-background"
                            strokeWidth={2}
                        />
                    </>
                )}
            </svg>
            {hovered && active !== null && (
                <div
                    className="pointer-events-none absolute -top-9 z-10 -translate-x-1/2 rounded-md border bg-background px-2 py-1 text-xs whitespace-nowrap shadow-sm"
                    style={{
                        left: `${Math.min(80, Math.max(20, (hovered.x / WIDTH) * 100))}%`,
                    }}
                >
                    <span className="font-mono font-semibold tabular-nums">
                        {data[active]}
                    </span>{' '}
                    {data[active] === 1 ? unit[0] : unit[1]}
                    <span className="text-muted-foreground">
                        {' · '}
                        {daysAgo === 0
                            ? 'hoy'
                            : daysAgo === 1
                              ? 'ayer'
                              : `hace ${daysAgo} días`}
                    </span>
                </div>
            )}
        </div>
    );
}

function Metric({
    icon: Icon,
    title,
    value,
    caption,
    trend,
    unit,
}: {
    icon: ComponentType<{ className?: string }>;
    title: string;
    value: number;
    caption: string;
    trend: number[];
    unit: [string, string];
}) {
    const recent = trend.reduce((sum, day) => sum + day, 0);

    return (
        <div className="space-y-5 rounded-lg border bg-card p-5">
            <div className="flex items-center gap-2">
                <Icon className="size-5 text-primary" aria-hidden />
                <dt className="text-base font-semibold">{title}</dt>
            </div>
            <div className="flex items-end justify-between gap-2.5">
                <div className="flex min-w-0 flex-col gap-1">
                    <span className="text-sm text-muted-foreground">
                        {caption}
                    </span>
                    <dd className="font-mono text-3xl font-bold tracking-tight tabular-nums">
                        {formatInteger(value)}
                    </dd>
                </div>
                <Sparkline
                    data={trend}
                    unit={unit}
                    label={`${recent} ${recent === 1 ? unit[0] : unit[1]} en los últimos ${trend.length} días`}
                />
            </div>
        </div>
    );
}

/**
 * Tres cifras de valor, cada una con su minigráfica de los últimos 28 días. El significado está en
 * el texto; la gráfica es un apoyo para ver el ritmo reciente.
 */
export default function ImpactMetrics({ stats }: { stats: HomeStats }) {
    const days = stats.trends.published.length;

    return (
        <section
            aria-label="La comunidad en cifras"
            className="surface-paper border-y"
        >
            <dl className="mx-auto grid w-full max-w-7xl gap-4 px-4 py-8 sm:px-6 md:grid-cols-3">
                <Metric
                    icon={Gift}
                    title="Objetos disponibles"
                    value={stats.available}
                    caption={`Publicados, últimos ${days} días`}
                    trend={stats.trends.published}
                    unit={['publicado', 'publicados']}
                />
                <Metric
                    icon={HeartHandshake}
                    title="Ya encontraron hogar"
                    value={stats.rehomed}
                    caption={`Entregas, últimos ${days} días`}
                    trend={stats.trends.rehomed}
                    unit={['entregado', 'entregados']}
                />
                <Metric
                    icon={Users}
                    title="Vecinos en la comunidad"
                    value={stats.neighbors}
                    caption={`Nuevos, últimos ${days} días`}
                    trend={stats.trends.neighbors}
                    unit={['vecino', 'vecinos']}
                />
            </dl>
        </section>
    );
}
