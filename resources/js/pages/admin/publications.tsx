import { Head, Link, router } from '@inertiajs/react';
import { Eye, EyeOff, PackageSearch, Search } from 'lucide-react';
import { useState } from 'react';
import AdminNav from '@/components/admin/admin-nav';
import ConfirmDialog from '@/components/confirm-dialog';
import EmptyState from '@/components/empty-state';
import Heading from '@/components/heading';
import PaginationNav from '@/components/pagination-nav';
import CoverImage from '@/components/publications/cover-image';
import ModalityLabel from '@/components/market/modality-label';
import StatusStamp from '@/components/market/status-stamp';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { formatRelative, pluralize } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import {
    hide,
    index as adminPublicationsIndex,
    unhide,
} from '@/routes/admin/publications';
import { show } from '@/routes/publications';
import type { Option, Paginated, PublicationCard } from '@/types';

type AdminPublication = PublicationCard & {
    owner: { name: string; email: string };
    is_hidden: boolean;
    hidden_reason: string | null;
    hidden_at: string | null;
    hidden_by: string | null;
};

type Filters = { q: string; status: string; visibility: string };

type Props = {
    publications: Paginated<AdminPublication>;
    filters: Filters;
    statuses: Option[];
};

const ALL = '__all__';

const visibilityOptions: Option[] = [
    { value: 'all', label: 'Todas' },
    { value: 'visible', label: 'Visibles' },
    { value: 'hidden', label: 'Ocultas' },
];

function queryFor(filters: Filters, page?: number) {
    const query: Record<string, string | number> = {};

    if (filters.q) {
        query.q = filters.q;
    }

    if (filters.status) {
        query.status = filters.status;
    }

    if (filters.visibility && filters.visibility !== 'all') {
        query.visibility = filters.visibility;
    }

    if (page && page > 1) {
        query.page = page;
    }

    return query;
}

export default function AdminPublications({
    publications: list,
    filters,
    statuses,
}: Props) {
    const [search, setSearch] = useState(filters.q);
    const [target, setTarget] = useState<AdminPublication | null>(null);
    const [reason, setReason] = useState('');
    const [processing, setProcessing] = useState(false);

    const apply = (next: Filters) =>
        router.get(adminPublicationsIndex.url(), queryFor(next), {
            preserveState: true,
            replace: true,
        });

    const confirmHide = () => {
        if (!target) {
            return;
        }

        router.post(
            hide.url(target.slug),
            { reason },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setTarget(null);
                    setReason('');
                },
            },
        );
    };

    return (
        <>
            <Head title="Publicaciones · Administración" />

            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Administración"
                    description="Modera las publicaciones: oculta las que incumplan las reglas."
                />
                <AdminNav />

                <form
                    role="search"
                    onSubmit={(e) => {
                        e.preventDefault();
                        apply({ ...filters, q: search.trim() });
                    }}
                    className="mb-5 grid gap-3 md:grid-cols-[1fr_11rem_11rem_auto]"
                >
                    <div className="relative">
                        <Search
                            className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden
                        />
                        <Input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por título o publicante…"
                            aria-label="Buscar publicaciones"
                            className="pl-9"
                            maxLength={100}
                        />
                    </div>
                    <Select
                        value={filters.status || ALL}
                        onValueChange={(value) =>
                            apply({
                                ...filters,
                                status: value === ALL ? '' : value,
                            })
                        }
                    >
                        <SelectTrigger aria-label="Filtrar por estado">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                Todos los estados
                            </SelectItem>
                            {statuses.map((status) => (
                                <SelectItem
                                    key={status.value}
                                    value={status.value}
                                >
                                    {status.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.visibility}
                        onValueChange={(value) =>
                            apply({ ...filters, visibility: value })
                        }
                    >
                        <SelectTrigger aria-label="Filtrar por visibilidad">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {visibilityOptions.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button type="submit">Buscar</Button>
                </form>

                <p className="mb-3 text-sm text-muted-foreground" role="status">
                    {pluralize(list.meta.total, 'publicación', 'publicaciones')}
                </p>

                {list.data.length === 0 ? (
                    <EmptyState
                        icon={PackageSearch}
                        title="No hay publicaciones con esos filtros"
                        description="Cambia la búsqueda o los filtros para ver más resultados."
                    />
                ) : (
                    <ul className="divide-y border-y">
                        {list.data.map((publication) => (
                            <li
                                key={publication.id}
                                className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center"
                            >
                                <CoverImage
                                    src={publication.cover_url}
                                    alt=""
                                    className="size-16 shrink-0 rounded-lg"
                                />
                                <div className="min-w-0 flex-1 space-y-1.5">
                                    <Link
                                        href={show(publication.slug)}
                                        className="block truncate font-medium hover:underline"
                                    >
                                        {publication.title}
                                    </Link>
                                    <p className="truncate text-sm text-muted-foreground">
                                        {publication.owner.name} ·{' '}
                                        {publication.owner.email} ·{' '}
                                        {formatRelative(publication.created_at)}
                                    </p>
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
                                                Oculta
                                            </Badge>
                                        )}
                                    </div>
                                    {publication.is_hidden && (
                                        <p className="text-xs text-muted-foreground">
                                            Ocultada
                                            {publication.hidden_by
                                                ? ` por ${publication.hidden_by}`
                                                : ''}
                                            {publication.hidden_reason
                                                ? `: ${publication.hidden_reason}`
                                                : '. Sin motivo indicado.'}
                                        </p>
                                    )}
                                </div>

                                {publication.is_hidden ? (
                                    <Button
                                        variant="outline"
                                        onClick={() =>
                                            router.delete(
                                                unhide.url(publication.slug),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        <Eye aria-hidden />
                                        Mostrar de nuevo
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        onClick={() => setTarget(publication)}
                                    >
                                        <EyeOff aria-hidden />
                                        Ocultar
                                    </Button>
                                )}
                            </li>
                        ))}
                    </ul>
                )}

                <PaginationNav
                    className="mt-6"
                    currentPage={list.meta.current_page}
                    lastPage={list.meta.last_page}
                    href={(page) =>
                        adminPublicationsIndex({
                            query: queryFor(filters, page),
                        })
                    }
                />
            </div>

            <ConfirmDialog
                open={target !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setTarget(null);
                        setReason('');
                    }
                }}
                processing={processing}
                destructive
                title="¿Ocultar esta publicación?"
                description={`«${target?.title ?? ''}» dejará de aparecer en el catálogo y solo su dueño y la administración podrán verla.`}
                confirmLabel="Ocultar publicación"
                onConfirm={confirmHide}
            >
                <div className="grid gap-2">
                    <Label htmlFor="hide-reason">
                        Motivo{' '}
                        <span className="font-normal text-muted-foreground">
                            (opcional)
                        </span>
                    </Label>
                    <Textarea
                        id="hide-reason"
                        value={reason}
                        onChange={(e) => setReason(e.target.value)}
                        maxLength={255}
                        rows={3}
                        placeholder="Ej.: Producto no admitido según los términos."
                    />
                    <p className="text-xs text-muted-foreground">
                        El dueño verá este motivo en «Mis publicaciones».
                    </p>
                </div>
            </ConfirmDialog>
        </>
    );
}

AdminPublications.layout = {
    breadcrumbs: [
        { title: 'Administración', href: dashboard() },
        { title: 'Publicaciones', href: adminPublicationsIndex() },
    ],
};
