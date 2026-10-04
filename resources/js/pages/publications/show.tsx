import { Head, Link } from '@inertiajs/react';
import {
    CalendarClock,
    EyeOff,
    Info,
    MapPin,
    Pencil,
    Repeat,
    ShieldAlert,
    Tag,
    Trash2,
    Wrench,
} from 'lucide-react';
import { useState } from 'react';
import DeletePublicationDialog from '@/components/publications/delete-publication-dialog';
import Gallery from '@/components/publications/gallery';
import ModalityBadge from '@/components/publications/modality-badge';
import PriceLabel from '@/components/publications/price-label';
import StatusBadge from '@/components/publications/status-badge';
import StatusMenu from '@/components/publications/status-menu';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useInitials } from '@/hooks/use-initials';
import { formatMonthYear, formatRelative } from '@/lib/format';
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

            <div className="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
                <Breadcrumb className="mb-6">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink asChild>
                                <Link href={index()}>Catálogo</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        {category.parent && (
                            <>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbLink asChild>
                                        <Link
                                            href={index({
                                                query: {
                                                    category:
                                                        category.parent.slug,
                                                },
                                            })}
                                        >
                                            {category.parent.name}
                                        </Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                            </>
                        )}
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{category.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                {publication.is_hidden && (
                    <Alert variant="destructive" className="mb-6">
                        <EyeOff />
                        <AlertTitle>Publicación oculta</AlertTitle>
                        <AlertDescription>
                            La administración ocultó esta publicación, por eso
                            no aparece en el catálogo. Solo tú puedes verla.
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-8 lg:grid-cols-[1.25fr_1fr]">
                    <div className="min-w-0 lg:col-start-1 lg:row-start-1">
                        <Gallery
                            key={publication.id}
                            images={publication.images}
                            title={publication.title}
                        />
                    </div>

                    <div className="space-y-5 lg:col-start-2 lg:row-span-2 lg:row-start-1">
                        <Card className="gap-4">
                            <CardHeader className="gap-3">
                                <div className="flex flex-wrap gap-2">
                                    <ModalityBadge
                                        modality={publication.modality.value}
                                        label={publication.modality.label}
                                    />
                                    <StatusBadge
                                        status={publication.status.value}
                                        label={publication.status.label}
                                    />
                                </div>
                                <h1 className="text-2xl leading-snug font-semibold tracking-tight text-balance">
                                    {publication.title}
                                </h1>
                                <PriceLabel
                                    modality={publication.modality.value}
                                    price={publication.price}
                                    className="text-3xl"
                                />
                            </CardHeader>
                            <CardContent className="space-y-5">
                                {publication.modality.value === 'exchange' &&
                                    publication.wanted_in_exchange && (
                                        <div className="flex gap-3 rounded-lg bg-exchange p-3 text-sm text-exchange-foreground">
                                            <Repeat
                                                className="mt-0.5 size-4 shrink-0"
                                                aria-hidden
                                            />
                                            <p>
                                                <strong>Busca a cambio:</strong>{' '}
                                                {publication.wanted_in_exchange}
                                            </p>
                                        </div>
                                    )}

                                <dl className="grid gap-3 text-sm">
                                    <Fact
                                        icon={Wrench}
                                        label="Estado del objeto"
                                    >
                                        {publication.condition.label}
                                    </Fact>
                                    <Fact icon={Tag} label="Categoría">
                                        {category.parent
                                            ? `${category.parent.name} › ${category.name}`
                                            : category.name}
                                    </Fact>
                                    <Fact icon={MapPin} label="Ubicación">
                                        {publication.location}
                                    </Fact>
                                    <Fact
                                        icon={CalendarClock}
                                        label="Publicado"
                                    >
                                        {formatRelative(publication.created_at)}
                                    </Fact>
                                </dl>

                                {unavailable && (
                                    <Alert>
                                        <Info />
                                        <AlertTitle className="line-clamp-none">
                                            Este objeto ya no está disponible
                                        </AlertTitle>
                                        <AlertDescription>
                                            Estado actual:{' '}
                                            {publication.status.label.toLowerCase()}
                                            .
                                        </AlertDescription>
                                    </Alert>
                                )}

                                <Alert className="border-primary/30 bg-accent/60">
                                    <ShieldAlert />
                                    <AlertTitle className="line-clamp-none">
                                        El pago y la entrega se coordinan
                                        directamente entre las partes
                                    </AlertTitle>
                                    <AlertDescription>
                                        ReUsa es solo un canal de contacto: no
                                        interviene en la negociación, el pago ni
                                        la entrega.{' '}
                                        <Link
                                            href={terms()}
                                            className="font-medium text-foreground underline underline-offset-4"
                                        >
                                            Leer términos
                                        </Link>
                                    </AlertDescription>
                                </Alert>

                                <div className="space-y-2">
                                    <Button
                                        className="w-full"
                                        size="lg"
                                        disabled
                                        aria-describedby="solicitud-ayuda"
                                    >
                                        Solicitudes disponibles próximamente
                                    </Button>
                                    <p
                                        id="solicitud-ayuda"
                                        className="text-center text-xs text-muted-foreground"
                                    >
                                        Muy pronto podrás pedir este objeto
                                        desde ReUsa.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="gap-3">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Publicado por
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex items-center gap-3">
                                <Avatar className="size-11">
                                    <AvatarFallback className="bg-accent font-medium text-accent-foreground">
                                        {getInitials(owner.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0 text-sm">
                                    <p className="truncate font-medium">
                                        {owner.name}
                                    </p>
                                    <p className="truncate text-muted-foreground">
                                        {owner.community
                                            ? `${owner.community} · `
                                            : ''}
                                        Miembro desde{' '}
                                        {formatMonthYear(owner.member_since)}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {publication.can.update && (
                            <Card className="gap-3 border-primary/30">
                                <CardHeader>
                                    <CardTitle className="text-base">
                                        Administrar mi publicación
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-wrap gap-2">
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
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <section
                        aria-labelledby="descripcion"
                        className="min-w-0 lg:col-start-1 lg:row-start-2"
                    >
                        <h2
                            id="descripcion"
                            className="mb-3 text-xl font-semibold"
                        >
                            Descripción
                        </h2>
                        <p className="leading-relaxed whitespace-pre-line text-foreground/90">
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

function Fact({
    icon: Icon,
    label,
    children,
}: {
    icon: typeof Tag;
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div>
            <dt className="flex items-center gap-2 text-xs text-muted-foreground">
                <Icon className="size-4 shrink-0" aria-hidden />
                {label}
            </dt>
            <dd className="pl-6 text-sm font-medium">{children}</dd>
        </div>
    );
}
