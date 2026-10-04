import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Camera,
    Gift,
    HandCoins,
    Handshake,
    Leaf,
    Repeat,
    Search,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import CategoryIcon from '@/components/publications/category-icon';
import PublicationCard from '@/components/publications/publication-card';
import { Button } from '@/components/ui/button';
import { pluralize } from '@/lib/format';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { create, index } from '@/routes/publications';
import type { Category, PublicationCard as PublicationCardType } from '@/types';

type Props = {
    latest: PublicationCardType[];
    categories: Category[];
    availableCount: number;
};

const steps: { icon: LucideIcon; title: string; text: string }[] = [
    {
        icon: Camera,
        title: 'Publica lo que ya no usas',
        text: 'Sube hasta 4 fotos, describe el objeto y elige si lo donas, lo intercambias o lo vendes.',
    },
    {
        icon: Search,
        title: 'Encuentra lo que necesitas',
        text: 'Explora el catálogo de tu comunidad y filtra por categoría, modalidad y estado.',
    },
    {
        icon: Handshake,
        title: 'Coordina con tu vecino',
        text: 'El pago y la entrega se acuerdan directamente entre las partes. ReUsa solo facilita el contacto.',
    },
];

const modalities: {
    value: string;
    icon: LucideIcon;
    title: string;
    text: string;
    tone: string;
}[] = [
    {
        value: 'donation',
        icon: Gift,
        title: 'Donar',
        text: 'Regala lo que otra persona sí va a aprovechar.',
        tone: 'bg-donation text-donation-foreground',
    },
    {
        value: 'exchange',
        icon: Repeat,
        title: 'Intercambiar',
        text: 'Cambia lo tuyo por algo que realmente necesitas.',
        tone: 'bg-exchange text-exchange-foreground',
    },
    {
        value: 'sale',
        icon: HandCoins,
        title: 'Vender',
        text: 'Pon un precio justo en pesos y recupera algo de valor.',
        tone: 'bg-sale text-sale-foreground',
    },
];

export default function Welcome({ latest, categories, availableCount }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Dona, intercambia y vende lo que ya no usas" />

            {/* Hero */}
            <section className="relative overflow-hidden border-b bg-gradient-to-b from-accent/70 to-background">
                <div
                    aria-hidden
                    className="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-primary/10 blur-3xl"
                />
                <div className="relative mx-auto grid w-full max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[1.2fr_1fr]">
                    <div className="space-y-6">
                        <span className="inline-flex items-center gap-2 rounded-full border bg-background/80 px-3 py-1 text-sm text-muted-foreground">
                            <Leaf className="size-4 text-primary" aria-hidden />
                            Economía circular para tu comunidad en Medellín
                        </span>
                        <h1 className="text-4xl leading-tight font-semibold tracking-tight text-balance sm:text-5xl">
                            Dale una segunda vida a lo que{' '}
                            <span className="text-primary">ya no usas</span>
                        </h1>
                        <p className="max-w-xl text-lg text-muted-foreground">
                            ReUsa conecta a vecinos para donar, intercambiar o
                            vender objetos en desuso. Menos residuos, más
                            comunidad.
                        </p>
                        <div className="flex flex-wrap gap-3">
                            <Button size="lg" asChild>
                                <Link href={index()}>
                                    Explorar el catálogo
                                    <ArrowRight aria-hidden />
                                </Link>
                            </Button>
                            <Button size="lg" variant="outline" asChild>
                                <Link href={auth.user ? create() : register()}>
                                    {auth.user
                                        ? 'Publicar un objeto'
                                        : 'Crear cuenta gratis'}
                                </Link>
                            </Button>
                        </div>
                        {availableCount > 0 && (
                            <p className="text-sm text-muted-foreground">
                                <strong className="text-foreground">
                                    {pluralize(
                                        availableCount,
                                        'objeto disponible',
                                        'objetos disponibles',
                                    )}
                                </strong>{' '}
                                esperan un nuevo hogar.
                            </p>
                        )}
                    </div>

                    <ul
                        className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1"
                        aria-label="Modalidades"
                    >
                        {modalities.map((item) => (
                            <li key={item.value}>
                                <Link
                                    href={index({
                                        query: { modality: item.value },
                                    })}
                                    className="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-xs transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                >
                                    <span
                                        className={cn(
                                            'flex size-11 shrink-0 items-center justify-center rounded-lg',
                                            item.tone,
                                        )}
                                    >
                                        <item.icon
                                            className="size-5"
                                            aria-hidden
                                        />
                                    </span>
                                    <span>
                                        <span className="block font-semibold">
                                            {item.title}
                                        </span>
                                        <span className="block text-sm text-muted-foreground">
                                            {item.text}
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* Cómo funciona */}
            <section
                aria-labelledby="como-funciona"
                className="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6"
            >
                <div className="mb-10 max-w-2xl space-y-2">
                    <h2
                        id="como-funciona"
                        className="text-3xl font-semibold tracking-tight"
                    >
                        Cómo funciona
                    </h2>
                    <p className="text-muted-foreground">
                        Tres pasos sencillos para dar y recibir.
                    </p>
                </div>
                <ol className="grid gap-6 md:grid-cols-3">
                    {steps.map((step, index) => (
                        <li
                            key={step.title}
                            className="relative rounded-xl border bg-card p-6"
                        >
                            <span className="absolute top-4 right-5 text-5xl font-semibold text-muted/80 select-none">
                                {index + 1}
                            </span>
                            <span className="mb-4 flex size-12 items-center justify-center rounded-lg bg-accent text-accent-foreground">
                                <step.icon className="size-6" aria-hidden />
                            </span>
                            <h3 className="mb-1 text-lg font-semibold">
                                {step.title}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                {step.text}
                            </p>
                        </li>
                    ))}
                </ol>
            </section>

            {/* Categorías */}
            <section
                aria-labelledby="categorias"
                className="border-y bg-muted/40"
            >
                <div className="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6">
                    <h2
                        id="categorias"
                        className="mb-6 text-2xl font-semibold tracking-tight"
                    >
                        Explora por categoría
                    </h2>
                    <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        {categories.map((category) => (
                            <li key={category.id}>
                                <Link
                                    href={index({
                                        query: { category: category.slug },
                                    })}
                                    className="flex h-full flex-col items-center gap-2 rounded-xl border bg-card p-4 text-center text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                >
                                    <CategoryIcon
                                        name={category.icon}
                                        className="size-6 text-primary"
                                    />
                                    {category.name}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* Últimas publicaciones */}
            <section
                aria-labelledby="ultimas"
                className="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6"
            >
                <div className="mb-8 flex flex-wrap items-end justify-between gap-3">
                    <h2
                        id="ultimas"
                        className="text-3xl font-semibold tracking-tight"
                    >
                        Últimas publicaciones
                    </h2>
                    <Button variant="ghost" asChild>
                        <Link href={index()}>
                            Ver todo el catálogo
                            <ArrowRight aria-hidden />
                        </Link>
                    </Button>
                </div>

                {latest.length > 0 ? (
                    <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        {latest.map((publication) => (
                            <li key={publication.id}>
                                <PublicationCard publication={publication} />
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                        Aún no hay objetos publicados. ¡Sé la primera persona en
                        compartir algo!
                    </p>
                )}
            </section>

            {/* Llamado final */}
            <section className="mx-auto w-full max-w-7xl px-4 pb-20 sm:px-6">
                <div className="flex flex-col items-start justify-between gap-6 rounded-2xl bg-primary px-8 py-10 text-primary-foreground md:flex-row md:items-center">
                    <div className="space-y-1">
                        <h2 className="text-2xl font-semibold">
                            ¿Tienes algo que ya no usas?
                        </h2>
                        <p className="text-primary-foreground/85">
                            Publícalo en minutos y ayuda a que siga útil en tu
                            comunidad.
                        </p>
                    </div>
                    <Button size="lg" variant="secondary" asChild>
                        <Link href={auth.user ? create() : register()}>
                            {auth.user ? 'Publicar un objeto' : 'Empezar ahora'}
                            <ArrowRight aria-hidden />
                        </Link>
                    </Button>
                </div>
            </section>
        </>
    );
}
