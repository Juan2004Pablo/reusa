import { Head, Link } from '@inertiajs/react';
import { EyeOff, Layers, Pencil, Store, Tag, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import StatusStamp from '@/components/market/status-stamp';
import DeletePublicationDialog from '@/components/publications/delete-publication-dialog';
import Gallery from '@/components/publications/gallery';
import StatusMenu from '@/components/publications/status-menu';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useInitials } from '@/hooks/use-initials';
import { formatMonthYear, formatRelative } from '@/lib/format';
import { neighborhood } from '@/lib/location';
import { terms } from '@/routes';
import { edit, index } from '@/routes/publications';
import type { PublicationDetail } from '@/types';

export default function PublicationShow({
    publication,
}: {
    publication: PublicationDetail;
}) {
    const getInitials = useInitials();
    const [confirmOpen, setConfirmOpen] = useState(false);
    const unavailable = publication.status.value !== 'available';
    const { category, owner } = publication;

    return (
        <>
            <Head title={publication.title} />

            <div className="mx-auto w-full max-w-[1600px] px-4 pt-8 pb-20 sm:px-6 md:pb-28">
                <nav aria-label="Ruta" className="mb-6">
                    <ol className="type-label flex flex-wrap items-center gap-x-2 gap-y-1 text-muted-foreground [&_svg]:size-3.5 [&_svg]:shrink-0">
                        <li>
                            <Link
                                href={index()}
                                className="inline-flex items-center gap-1.5 hover:text-foreground hover:underline"
                            >
                                <Store aria-hidden />
                                Catálogo
                            </Link>
                        </li>
                        {category.parent && (
                            <>
                                <li aria-hidden>/</li>
                                <li>
                                    <Link
                                        href={index({
                                            query: {
                                                category: category.parent.slug,
                                            },
                                        })}
                                        className="inline-flex items-center gap-1.5 hover:text-foreground hover:underline"
                                    >
                                        <Layers aria-hidden />
                                        {category.parent.name}
                                    </Link>
                                </li>
                            </>
                        )}
                        <li aria-hidden>/</li>
                        <li
                            aria-current="page"
                            className="inline-flex items-center gap-1.5 text-foreground"
                        >
                            <Tag aria-hidden />
                            {category.name}
                        </li>
                    </ol>
                </nav>

                {publication.is_hidden && (
                    <Alert variant="destructive" className="mb-6">
                        <EyeOff />
                        <AlertTitle className="line-clamp-none">
                            Publicación oculta
                        </AlertTitle>
                        <AlertDescription>
                            La administración ocultó esta publicación, por eso
                            no aparece en el catálogo. Solo tú puedes verla.
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-x-12 gap-y-10 lg:grid-cols-[1.2fr_1fr]">
                    <div className="relative min-w-0 lg:col-start-1 lg:row-start-1">
                        <Gallery
                            key={publication.id}
                            images={publication.images}
                            title={publication.title}
                        />
                        {unavailable && (
                            <StatusStamp
                                status={publication.status.value}
                                label={publication.status.label}
                                tilt
                                size="lg"
                                className="pointer-events-none absolute top-5 right-5"
                            />
                        )}
                    </div>

                    <div className="flex min-w-0 flex-col gap-7 lg:col-start-2 lg:row-span-2 lg:row-start-1">
                        <header className="space-y-4">
                            <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                                <ModalityLabel
                                    modality={publication.modality.value}
                                    label={publication.modality.label}
                                />
                                <span className="type-label text-muted-foreground">
                                    {publication.status.label}
                                </span>
                            </div>
                            <h1 className="type-display text-3xl sm:text-4xl">
                                {publication.title}
                            </h1>
                            <PriceTag
                                modality={publication.modality.value}
                                price={publication.price}
                                size="lg"
                            />
                        </header>

                        {publication.modality.value === 'exchange' &&
                            publication.wanted_in_exchange && (
                                <div className="border-l-4 border-exchange pl-4">
                                    <p className="type-label text-muted-foreground">
                                        Busca a cambio
                                    </p>
                                    <p className="mt-1 text-lg">
                                        {publication.wanted_in_exchange}
                                    </p>
                                </div>
                            )}

                        <section aria-labelledby="ficha">
                            <h2 id="ficha" className="type-label mb-3">
                                Ficha del objeto
                            </h2>
                            <dl className="space-y-2.5 border-t border-foreground/80 pt-3 text-sm">
                                <FichaRow label="Estado del objeto">
                                    {publication.condition.label}
                                </FichaRow>
                                <FichaRow label="Categoría">
                                    {category.name}
                                </FichaRow>
                                <FichaRow label="Barrio">
                                    {neighborhood(publication.location)}
                                </FichaRow>
                                <FichaRow label="Punto de entrega">
                                    {publication.location}
                                </FichaRow>
                                <FichaRow label="Publicado">
                                    {formatRelative(publication.created_at)}
                                </FichaRow>
                            </dl>
                        </section>

                        <aside
                            aria-label="Pago y entrega"
                            className="paper-note mt-2 space-y-1.5 px-5 pt-5 pb-4"
                        >
                            <p className="font-semibold">
                                El pago y la entrega se coordinan directamente
                                entre las partes.
                            </p>
                            <p className="text-sm">
                                ReUsa solo pone en contacto a los vecinos: no
                                interviene en la negociación, el pago ni la
                                entrega.{' '}
                                <Link
                                    href={terms()}
                                    className="font-semibold underline underline-offset-4"
                                >
                                    Leer términos
                                </Link>
                            </p>
                        </aside>

                        <div className="space-y-2">
                            <Button
                                className="h-12 w-full text-base"
                                disabled
                                aria-describedby="solicitud-ayuda"
                            >
                                Solicitudes disponibles próximamente
                            </Button>
                            <p
                                id="solicitud-ayuda"
                                className="text-center text-sm text-muted-foreground"
                            >
                                Muy pronto podrás pedir este objeto desde ReUsa.
                            </p>
                        </div>

                        <section
                            aria-labelledby="publicante"
                            className="flex items-center gap-4 border-t pt-5"
                        >
                            <span
                                className="flex size-12 shrink-0 items-center justify-center rounded-full border-2 border-foreground/80 font-mono text-sm font-semibold"
                                aria-hidden
                            >
                                {getInitials(owner.name)}
                            </span>
                            <div className="min-w-0">
                                <h2
                                    id="publicante"
                                    className="type-label text-muted-foreground"
                                >
                                    Publicado por
                                </h2>
                                <p className="truncate font-semibold">
                                    {owner.name}
                                </p>
                                <p className="type-label truncate text-muted-foreground">
                                    {owner.community
                                        ? `${owner.community} · `
                                        : ''}
                                    Vecino desde{' '}
                                    {formatMonthYear(owner.member_since)}
                                </p>
                            </div>
                        </section>

                        {publication.can.update && (
                            <section
                                aria-labelledby="administrar"
                                className="space-y-3 rounded-md border border-dashed border-input bg-paper/60 p-4"
                            >
                                <h2 id="administrar" className="type-label">
                                    Tu publicación
                                </h2>
                                <div className="flex flex-wrap gap-2">
                                    <Button variant="outline" asChild>
                                        <Link href={edit(publication.slug)}>
                                            <Pencil aria-hidden />
                                            Editar
                                        </Link>
                                    </Button>
                                    {publication.can.change_status && (
                                        <StatusMenu
                                            slug={publication.slug}
                                            options={publication.status_options}
                                        />
                                    )}
                                    {publication.can.delete && (
                                        <Button
                                            variant="ghost"
                                            className="text-destructive hover:text-destructive"
                                            onClick={() => setConfirmOpen(true)}
                                        >
                                            <Trash2 aria-hidden />
                                            Eliminar
                                        </Button>
                                    )}
                                </div>
                            </section>
                        )}
                    </div>

                    <section
                        aria-labelledby="descripcion"
                        className="min-w-0 lg:col-start-1 lg:row-start-2"
                    >
                        <h2 id="descripcion" className="type-label mb-3">
                            Descripción
                        </h2>
                        <p className="max-w-prose border-t border-foreground/80 pt-4 text-lg leading-relaxed whitespace-pre-line">
                            {publication.description}
                        </p>
                    </section>
                </div>
            </div>

            <DeletePublicationDialog
                slug={publication.slug}
                title={publication.title}
                open={confirmOpen}
                onOpenChange={setConfirmOpen}
            />
        </>
    );
}

/** Fila de la ficha: «Dato ······ Valor», como una etiqueta impresa. */
function FichaRow({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div className="label-row">
            <dt className="text-muted-foreground">{label}</dt>
            <span className="leader" aria-hidden />
            <dd className="font-medium">{children}</dd>
        </div>
    );
}
