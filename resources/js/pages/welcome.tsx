import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, Search } from 'lucide-react';
import { useState } from 'react';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import SectionHeading from '@/components/market/section-heading';
import CategoryIcon from '@/components/publications/category-icon';
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
    categories: (Category & { available_count: number })[];
    availableCount: number;
    stats: { available: number; rehomed: number; neighbors: number };
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

const tilts = ['-rotate-[1.4deg]', 'rotate-[1.1deg]', '-rotate-[0.6deg]'];

export default function Welcome({ latest, categories, stats }: Props) {
    const { auth } = usePage().props;
    const [query, setQuery] = useState('');
    const table = latest.slice(0, 3);

    const search = (event: React.FormEvent) => {
        event.preventDefault();
        router.get(index.url(), query.trim() ? { q: query.trim() } : {});
    };

    return (
        <>
            <Head title="Dona, intercambia y vende lo que ya no usas" />

            {/* Portada */}
            <section className="border-b">
                <div className="mx-auto grid w-full max-w-7xl gap-12 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[1.15fr_1fr] lg:items-center">
                    <div className="space-y-7">
                        <p className="type-label text-muted-foreground">
                            Economía circular entre vecinos · Medellín
                        </p>
                        <h1 className="type-display text-[2.6rem] sm:text-6xl">
                            Lo que ya no usas, le sirve a un vecino.
                        </h1>
                        <p className="max-w-xl text-lg text-muted-foreground">
                            Dona, intercambia o vende objetos en buen estado
                            dentro de tu comunidad. Menos residuos, más
                            vecindario.
                        </p>

                        <form
                            role="search"
                            onSubmit={search}
                            className="flex max-w-xl gap-2"
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

                        <ul className="flex flex-wrap gap-x-6 gap-y-2">
                            {modalities.map((item) => (
                                <li key={item.value}>
                                    <Link
                                        href={index({
                                            query: { modality: item.value },
                                        })}
                                        className="type-label inline-flex items-center gap-2 underline-offset-4 hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                    >
                                        <span
                                            className={cn(
                                                'size-2 rounded-full',
                                                item.dot,
                                            )}
                                            aria-hidden
                                        />
                                        {item.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {table.length > 0 && (
                        <div className="relative">
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
                                                    label={item.modality.label}
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
                                                modality={item.modality.value}
                                                price={item.price}
                                                className="shrink-0 [--tag-hole:var(--card)]"
                                            />
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </section>

            {/* Cifras reales */}
            <section
                aria-label="La comunidad en cifras"
                className="border-b bg-paper"
            >
                <dl className="mx-auto grid w-full max-w-7xl grid-cols-3 divide-x px-4 sm:px-6">
                    {[
                        ['Objetos disponibles', stats.available],
                        ['Ya encontraron hogar', stats.rehomed],
                        ['Vecinos en la comunidad', stats.neighbors],
                    ].map(([label, value]) => (
                        <div
                            key={label}
                            className="space-y-1 px-3 py-6 first:pl-0 sm:px-8 sm:first:pl-0"
                        >
                            <dd className="font-mono text-3xl font-semibold tabular-nums sm:text-5xl">
                                {formatInteger(value as number)}
                            </dd>
                            <dt className="type-label text-muted-foreground">
                                {label}
                            </dt>
                        </div>
                    ))}
                </dl>
            </section>

            {/* Cómo funciona */}
            <section
                aria-labelledby="como-funciona"
                className="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6"
            >
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
            </section>

            {/* Estantería de categorías */}
            <section
                aria-labelledby="categorias"
                className="mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6"
            >
                <SectionHeading
                    id="categorias"
                    label="Estantería"
                    title="Qué hay por categoría"
                />
                <ul className="grid divide-y sm:grid-cols-2 sm:gap-x-10 lg:grid-cols-4">
                    {categories.map((category) => (
                        <li key={category.id}>
                            <Link
                                href={index({
                                    query: { category: category.slug },
                                })}
                                className="group flex items-center gap-4 py-4 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                            >
                                <CategoryIcon
                                    name={category.icon}
                                    className="size-6 shrink-0 text-primary"
                                />
                                <span className="min-w-0 flex-1 leading-snug font-medium group-hover:underline">
                                    {category.name}
                                </span>
                                <span className="type-label shrink-0 text-muted-foreground tabular-nums">
                                    {category.available_count}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            </section>

            {/* Últimas publicaciones */}
            <section
                aria-labelledby="ultimas"
                className="mx-auto w-full max-w-7xl px-4 pb-20 sm:px-6"
            >
                <SectionHeading
                    id="ultimas"
                    label="Recién llegados"
                    title="Últimas publicaciones"
                    action={
                        <Button variant="ghost" asChild>
                            <Link href={index()}>
                                Ver todo el catálogo
                                <ArrowRight aria-hidden />
                            </Link>
                        </Button>
                    }
                />
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
            </section>

            {/* Cierre */}
            <section className="border-t bg-primary text-primary-foreground">
                <div className="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-6 px-4 py-12 sm:px-6">
                    <p className="type-display max-w-2xl text-3xl sm:text-4xl">
                        ¿Tienes algo guardado que ya no usas?
                    </p>
                    <Button size="lg" variant="secondary" asChild>
                        <Link href={auth.user ? create() : register()}>
                            {auth.user
                                ? 'Publicar un objeto'
                                : 'Crear cuenta y publicar'}
                            <ArrowRight aria-hidden />
                        </Link>
                    </Button>
                </div>
            </section>
        </>
    );
}
