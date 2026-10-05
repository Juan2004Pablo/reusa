import { Head, Link } from '@inertiajs/react';
import {
    Eye,
    EyeOff,
    MoreVertical,
    Package,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import EmptyState from '@/components/empty-state';
import Heading from '@/components/heading';
import PaginationNav from '@/components/pagination-nav';
import CoverImage from '@/components/publications/cover-image';
import DeletePublicationDialog from '@/components/publications/delete-publication-dialog';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import StatusStamp from '@/components/market/status-stamp';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatRelative } from '@/lib/format';
import { index as myPublications } from '@/routes/my-publications';
import { create, edit, show } from '@/routes/publications';
import { update as updateStatus } from '@/routes/publications/status';
import type { MyPublication, Paginated, PublicationStatusValue } from '@/types';

type Props = {
    publications: Paginated<MyPublication>;
    counts: Record<PublicationStatusValue, number>;
};

const summary: { key: PublicationStatusValue; label: string }[] = [
    { key: 'available', label: 'Disponibles' },
    { key: 'reserved', label: 'Reservadas' },
    { key: 'delivered', label: 'Entregadas' },
    { key: 'sold', label: 'Vendidas' },
];

export default function MyPublications({ publications, counts }: Props) {
    const [toDelete, setToDelete] = useState<MyPublication | null>(null);

    return (
        <>
            <Head title="Mis publicaciones" />

            <div className="mx-auto w-full max-w-[1400px] space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title="Mis publicaciones"
                        description="Gestiona los objetos que compartes con tu comunidad."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus aria-hidden />
                            Publicar objeto
                        </Link>
                    </Button>
                </div>

                <dl className="grid grid-cols-2 divide-x divide-y border-y sm:grid-cols-4 sm:divide-y-0">
                    {summary.map((item) => (
                        <div
                            key={item.key}
                            className="space-y-1 px-4 py-4 first:pl-0"
                        >
                            <dd className="font-mono text-3xl font-semibold tabular-nums">
                                {counts[item.key]}
                            </dd>
                            <dt className="type-label text-muted-foreground">
                                {item.label}
                            </dt>
                        </div>
                    ))}
                </dl>

                {publications.data.length === 0 ? (
                    <EmptyState
                        icon={Package}
                        title="Aún no has publicado nada"
                        description="Publica el primer objeto que ya no uses: regálalo, cámbialo o véndelo a alguien de tu comunidad."
                    >
                        <Button asChild>
                            <Link href={create()}>
                                <Plus aria-hidden />
                                Publicar mi primer objeto
                            </Link>
                        </Button>
                    </EmptyState>
                ) : (
                    <ul className="divide-y border-y">
                        {publications.data.map((publication) => (
                            <li
                                key={publication.id}
                                className="flex items-center gap-4 p-3 sm:p-4"
                            >
                                <Link
                                    href={show(publication.slug)}
                                    className="shrink-0"
                                    tabIndex={-1}
                                    aria-hidden
                                >
                                    <CoverImage
                                        src={publication.cover_url}
                                        alt=""
                                        className="size-16 rounded-md sm:size-20"
                                    />
                                </Link>

                                <div className="min-w-0 flex-1 space-y-1.5">
                                    <Link
                                        href={show(publication.slug)}
                                        className="block truncate font-medium hover:underline"
                                    >
                                        {publication.title}
                                    </Link>
                                    <div className="flex flex-wrap items-center gap-1.5">
                                        <ModalityLabel
                                            modality={
                                                publication.modality.value
                                            }
                                            label={publication.modality.label}
                                        />
                                        {publication.status.value ===
                                        'available' ? (
                                            <span className="type-label text-muted-foreground">
                                                {publication.status.label}
                                            </span>
                                        ) : (
                                            <StatusStamp
                                                status={
                                                    publication.status.value
                                                }
                                                label={publication.status.label}
                                            />
                                        )}
                                        {publication.is_hidden && (
                                            <Badge variant="destructive">
                                                <EyeOff aria-hidden />
                                                Oculta por la administración
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="flex flex-wrap items-center gap-x-3 text-sm text-muted-foreground">
                                        <PriceTag
                                            modality={
                                                publication.modality.value
                                            }
                                            price={publication.price}
                                        />
                                        <span>
                                            {formatRelative(
                                                publication.created_at,
                                            )}
                                        </span>
                                    </p>
                                    {publication.is_hidden &&
                                        publication.hidden_reason && (
                                            <p className="text-xs text-destructive">
                                                Motivo:{' '}
                                                {publication.hidden_reason}
                                            </p>
                                        )}
                                </div>

                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Acciones para ${publication.title}`}
                                        >
                                            <MoreVertical aria-hidden />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        className="min-w-52"
                                    >
                                        <DropdownMenuItem asChild>
                                            <Link href={show(publication.slug)}>
                                                <Eye aria-hidden />
                                                Ver publicación
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link href={edit(publication.slug)}>
                                                <Pencil aria-hidden />
                                                Editar
                                            </Link>
                                        </DropdownMenuItem>
                                        {publication.status_options.length >
                                            0 && (
                                            <>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuLabel>
                                                    Marcar como…
                                                </DropdownMenuLabel>
                                                {publication.status_options.map(
                                                    (option) => (
                                                        <DropdownMenuItem
                                                            key={option.value}
                                                            onSelect={() =>
                                                                router.patch(
                                                                    updateStatus.url(
                                                                        publication.slug,
                                                                    ),
                                                                    {
                                                                        status: option.value,
                                                                    },
                                                                    {
                                                                        preserveScroll: true,
                                                                    },
                                                                )
                                                            }
                                                        >
                                                            {option.label}
                                                        </DropdownMenuItem>
                                                    ),
                                                )}
                                            </>
                                        )}
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            variant="destructive"
                                            onSelect={() =>
                                                setToDelete(publication)
                                            }
                                        >
                                            <Trash2 aria-hidden />
                                            Eliminar
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </li>
                        ))}
                    </ul>
                )}

                <PaginationNav
                    currentPage={publications.meta.current_page}
                    lastPage={publications.meta.last_page}
                    href={(page) => myPublications({ query: { page } })}
                />
            </div>

            {toDelete && (
                <DeletePublicationDialog
                    slug={toDelete.slug}
                    title={toDelete.title}
                    open
                    onOpenChange={(open) => !open && setToDelete(null)}
                />
            )}
        </>
    );
}

MyPublications.layout = {
    breadcrumbs: [{ title: 'Mis publicaciones', href: myPublications() }],
};
