import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, Search } from 'lucide-react';
import { useState } from 'react';
import ImpactMetrics from '@/components/market/impact-metrics';
import type { HomeStats } from '@/components/market/impact-metrics';
import CategoryTile from '@/components/market/category-tile';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import RotatingPhrase from '@/components/market/rotating-phrase';
import SectionHeading from '@/components/market/section-heading';
import CoverImage from '@/components/publications/cover-image';
import PublicationCard from '@/components/publications/publication-card';
import { Button } from '@/components/ui/button';
import { formatInteger } from '@/lib/format';
import { neighborhood } from '@/lib/location';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { create, index, show } from '@/routes/publications';
import type { Category, PublicationCard as PublicationCardType } from '@/types';

type Props = {
    latest: PublicationCardType[];
    categories: (Category & {
        available_count: number;
        cover_url: string | null;
    })[];
    availableCount: number;
    stats: HomeStats;
};

const steps = [
    {
        title: 'Publica',
        text: 'Sube hasta 4 fotos, describe el objeto y elige si lo donas, lo intercambias o lo vendes.',
    },
    {
        title: 'Encuentra',
        text: 'Explora el catálogo de tu comunidad y filtra por categoría, modalidad y estado.',
    },
    {
        title: 'Acuerda',
        text: 'El pago y la entrega los coordinan las dos personas. ReUsa solo facilita el contacto.',
    },
];

const modalities = [
    { value: 'donation', label: 'Donar', dot: 'bg-donation' },
    { value: 'exchange', label: 'Intercambiar', dot: 'bg-exchange' },
    { value: 'sale', label: 'Vender', dot: 'bg-sale' },
];

const heroPhrases = [
    'Lo que ya no usas, le sirve a un vecino.',
    'Esa bici del balcón puede volver a rodar.',
    'Tus libros leídos buscan un nuevo lector.',
    'Lo que a ti te sobra, a alguien le hace falta.',
    'Dale otra vida a la ropa que ya no usas.',
];

const tilts = ['-rotate-[0.5deg]', 'rotate-[0.4deg]', '-rotate-[0.3deg]'];

export default function Welcome({ latest, categories, stats }: Props) {
    const { auth } = usePage().props;
    const [query, setQuery] = useState('');
    const table = latest.slice(0, 3);
    const shelf = [...categories].sort(
        (a, b) => b.available_count - a.available_count,
    );
    const topCount = Math.max(1, ...shelf.map((c) => c.available_count));

    const search = (event: React.FormEvent) => {
        event.preventDefault();
        router.get(index.url(), query.trim() ? { q: query.trim() } : {});
    };

    return (
        <>
            <Head title="Dona, intercambia y vende lo que ya no usas" />

            {/* Portada */}
            <section className="surface-dawn">
                <div className="mx-auto grid w-full max-w-[1600px] gap-12 px-4 py-12 sm:px-6 md:py-16 lg:min-h-[88svh] lg:py-16 lg:grid-cols-[1.15fr_1fr] lg:items-center">
                    <div className="min-w-0 space-y-7">
                        <p className="type-label text-muted-foreground">
                            Economía circular entre vecinos · Medellín
                        </p>
                        <RotatingPhrase
                            as="h1"
                            phrases={heroPhrases}
                            interval={6000}
                            className="type-display text-[2.6rem] leading-[1.02] sm:text-7xl lg:text-8xl"
                        />
                        <p className="max-w-3xl text-xl text-muted-foreground">
                            <Link
                                href={index({
                                    query: { modality: 'donation' },
                                })}
                                className="font-semibold text-foreground underline decoration-donation decoration-2 underline-offset-4"
                            >
                                Dona
                            </Link>
                            ,{' '}
                            <Link
                                href={index({
                                    query: { modality: 'exchange' },
                                })}
                                className="font-semibold text-foreground underline decoration-exchange decoration-2 underline-offset-4"
                            >
                                intercambia
                            </Link>{' '}
                            o{' '}
                            <Link
                                href={index({ query: { modality: 'sale' } })}
                                className="font-semibold text-foreground underline decoration-sale decoration-2 underline-offset-4"
                            >
                                vende
                            </Link>{' '}
                            objetos en buen estado dentro de tu comunidad.
                            <span className="hidden sm:inline">
                                {' '}
                                Publica con fotos en minutos, encuentra lo que
                                necesitas a pocas cuadras y acuerda directamente
                                con tus vecinos.
                            </span>{' '}
                            Menos residuos, más vecindario.
                        </p>

                        <div className="flex flex-wrap items-center gap-x-8 gap-y-5 py-1 lg:pt-5">
                            <Link
                                href={index()}
                                className="cta-stamp cta-stamp-primary group max-sm:w-full max-sm:justify-between"
                            >
                                Ver el catálogo completo
                                <ArrowRight
                                    className="size-6 transition-transform duration-300 group-hover:translate-x-1 motion-reduce:transition-none"
                                    strokeWidth={2.5}
                                    aria-hidden
                                />
                            </Link>
                            <Button
                                variant="outline"
                                size="lg"
                                className="h-11 border-foreground/40 px-5"
                                asChild
                            >
                                <Link href={auth.user ? create() : register()}>
                                    Publicar un objeto
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div className="relative min-w-0">
                        <form
                            role="search"
                            onSubmit={search}
                            className="mb-8 flex gap-2"
                        >
                            <div className="relative flex-1">
                                <Search
                                    className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                                    aria-hidden
                                />
                                <input
                                    id="home-search"
                                    type="search"
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder="¿Qué estás buscando?"
                                    aria-label="Buscar objetos"
                                    maxLength={100}
                                    className="h-12 w-full rounded-md border border-input bg-card pr-3 pl-10 text-base outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                />
                            </div>
                            <Button
                                type="submit"
                                size="lg"
                                className="h-12 px-6"
                            >
                                Buscar
                            </Button>
                        </form>
                        {table.length > 0 && (
                            <>
                                <p className="type-label mb-4 text-muted-foreground">
                                    En la mesa hoy
                                </p>
                                <ul className="space-y-4">
                                    {table.map((item, i) => (
                                        <li
                                            key={item.id}
                                            className={cn(
                                                'transition-transform hover:rotate-0 motion-reduce:transform-none lg:ml-[calc(var(--shift)*1rem)]',
                                                tilts[i],
                                            )}
                                            style={
                                                {
                                                    '--shift': i % 2,
                                                } as React.CSSProperties
                                            }
                                        >
                                            <Link
                                                href={show(item.slug)}
                                                className="group flex items-center gap-4 rounded-md border bg-card p-3 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                            >
                                                <CoverImage
                                                    src={item.cover_url}
                                                    alt=""
                                                    className="size-20 shrink-0 rounded-sm"
                                                />
                                                <span className="min-w-0 flex-1 space-y-1.5">
                                                    <ModalityLabel
                                                        modality={
                                                            item.modality.value
                                                        }
                                                        label={
                                                            item.modality.label
                                                        }
                                                    />
                                                    <span className="block truncate font-semibold group-hover:underline">
                                                        {item.title}
                                                    </span>
                                                    <span className="type-label block text-muted-foreground">
                                                        {neighborhood(
                                                            item.location,
                                                        )}
                                                    </span>
                                                </span>
                                                <PriceTag
                                                    modality={
                                                        item.modality.value
                                                    }
                                                    price={item.price}
                                                    className="shrink-0 [--tag-hole:var(--card)]"
                                                />
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                                <div className="mt-5 flex justify-end">
                                    <Link
                                        href={index()}
                                        className="type-label underline underline-offset-4 hover:text-primary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                    >
                                        Ver más
                                    </Link>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </section>

            {/* Cifras reales */}
            {/* <ImpactMetrics stats={stats} /> */}

            {/* Catálogo */}
            <section
                aria-labelledby="catalogo"
                className="mx-auto w-full max-w-[1600px] px-4 py-20 sm:px-6"
            >
                <SectionHeading
                    id="catalogo"
                    size="xl"
                    label="El catálogo"
                    title={`${formatInteger(stats.available)} ${stats.available === 1 ? 'objeto espera' : 'objetos esperan'} un nuevo hogar`}
                    description="Todo lo que tus vecinos donan, intercambian o venden ahora mismo, con fotos y a pocas cuadras."
                    action={
                        <Button size="lg" className="h-12 px-6" asChild>
                            <Link href={index()}>
                                Explorar el catálogo
                                <ArrowRight aria-hidden />
                            </Link>
                        </Button>
                    }
                />
                <nav
                    aria-label="Filtrar el catálogo por modalidad"
                    className="flex flex-wrap items-center gap-x-6 gap-y-2 pt-6"
                >
                    <span className="type-label text-muted-foreground">
                        Filtrar por
                    </span>
                    {modalities.map((item) => (
                        <Link
                            key={item.value}
                            href={index({ query: { modality: item.value } })}
                            className="type-label inline-flex items-center gap-2 underline-offset-4 hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            <span
                                className={cn('size-2 rounded-full', item.dot)}
                                aria-hidden
                            />
                            {item.label}
                        </Link>
                    ))}
                </nav>
                <div className="pt-8">
                    {latest.length > 0 ? (
                        <ul className="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                            {latest.map((publication) => (
                                <li key={publication.id}>
                                    <PublicationCard
                                        publication={publication}
                                    />
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="rounded-md border border-dashed p-10 text-muted-foreground">
                            Aún no hay objetos publicados. Puedes ser la primera
                            persona en compartir algo.
                        </p>
                    )}
                </div>
                <Link
                    href={index()}
                    className="group type-display mt-14 flex items-center justify-between gap-6 border-y-2 border-foreground py-6 text-2xl transition-colors hover:bg-foreground hover:px-5 hover:text-background focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none motion-reduce:transition-none sm:text-4xl"
                >
                    Ver los {formatInteger(stats.available)} objetos del
                    catálogo
                    <ArrowRight
                        className="size-8 shrink-0 transition-transform duration-300 group-hover:translate-x-1 motion-reduce:transition-none"
                        aria-hidden
                    />
                </Link>
            </section>

            {/* Estantería de categorías */}
            <section aria-labelledby="categorias" className="surface-paper">
                <div className="mx-auto w-full max-w-[1600px] px-4 py-16 sm:px-6">
                    <SectionHeading
                        id="categorias"
                        label="Estantería"
                        title="Qué hay por categoría"
                        description="Ordenadas por la cantidad de objetos que hay hoy: las más movidas van primero."
                    />
                    <ul className="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-12 [&>li]:transition-opacity [&>li]:duration-300 [@media(hover:hover)]:[&:hover>li:not(:hover)]:opacity-45">
                        {shelf.map((category, i) => (
                            <li
                                key={category.id}
                                className={cn(
                                    'flex',
                                    i < 2
                                        ? 'col-span-2 lg:col-span-6'
                                        : 'lg:col-span-4',
                                )}
                            >
                                <CategoryTile
                                    category={category}
                                    featured={i < 2}
                                    className="w-full"
                                />
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* Cómo funciona */}
            <section aria-labelledby="como-funciona">
                <div className="mx-auto w-full max-w-[1600px] px-4 pt-16 sm:px-6">
                    <SectionHeading
                        id="como-funciona"
                        label="Tres pasos"
                        title="Cómo funciona"
                    />
                    <ol className="grid divide-y md:grid-cols-3 md:divide-x md:divide-y-0">
                        {steps.map((step, i) => (
                            <li
                                key={step.title}
                                className="space-y-3 py-7 md:px-8 md:first:pl-0 md:last:pr-0"
                            >
                                <span className="font-mono text-sm font-semibold text-primary">
                                    {String(i + 1).padStart(2, '0')}
                                </span>
                                <h3 className="type-display text-2xl">
                                    {step.title}
                                </h3>
                                <p className="max-w-sm text-muted-foreground">
                                    {step.text}
                                </p>
                            </li>
                        ))}
                    </ol>
                </div>
            </section>
        </>
    );
}
