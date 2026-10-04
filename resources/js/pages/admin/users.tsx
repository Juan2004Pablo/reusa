import { Head, router } from '@inertiajs/react';
import { Search, ShieldCheck, UserX } from 'lucide-react';
import { useState } from 'react';
import AdminNav from '@/components/admin/admin-nav';
import ConfirmDialog from '@/components/confirm-dialog';
import EmptyState from '@/components/empty-state';
import Heading from '@/components/heading';
import PaginationNav from '@/components/pagination-nav';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatMonthYear, pluralize } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { update as updateRole } from '@/routes/admin/users/role';
import { update as updateStatus } from '@/routes/admin/users/status';
import type { Option, Paginated } from '@/types';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    community: string | null;
    role: { value: string; label: string };
    is_active: boolean;
    is_self: boolean;
    publications_count: number;
    created_at: string | null;
};

type Props = {
    users: Paginated<AdminUser>;
    filters: { q: string };
    roles: Option[];
};

type Pending =
    | { kind: 'status'; user: AdminUser; active: boolean }
    | { kind: 'role'; user: AdminUser; role: Option };

export default function AdminUsers({ users: list, filters, roles }: Props) {
    const [search, setSearch] = useState(filters.q);
    const [pending, setPending] = useState<Pending | null>(null);
    const [processing, setProcessing] = useState(false);

    const submitSearch = (event: React.FormEvent) => {
        event.preventDefault();
        router.get(
            usersIndex.url(),
            search.trim() ? { q: search.trim() } : {},
            { preserveState: true, replace: true },
        );
    };

    const confirm = () => {
        if (!pending) {
            return;
        }

        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        };

        if (pending.kind === 'status') {
            router.patch(
                updateStatus.url(pending.user.id),
                { is_active: pending.active },
                options,
            );
        } else {
            router.patch(
                updateRole.url(pending.user.id),
                { role: pending.role.value },
                options,
            );
        }
    };

    return (
        <>
            <Head title="Usuarios · Administración" />

            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Administración"
                    description="Gestiona las cuentas de la comunidad."
                />
                <AdminNav />

                <form
                    role="search"
                    onSubmit={submitSearch}
                    className="mb-5 flex gap-2"
                >
                    <div className="relative flex-1">
                        <Search
                            className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden
                        />
                        <Input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por nombre o correo…"
                            aria-label="Buscar usuarios"
                            className="pl-9"
                            maxLength={100}
                        />
                    </div>
                    <Button type="submit">Buscar</Button>
                </form>

                <p className="mb-3 text-sm text-muted-foreground" role="status">
                    {pluralize(list.meta.total, 'usuario', 'usuarios')}
                </p>

                {list.data.length === 0 ? (
                    <EmptyState
                        icon={UserX}
                        title="No se encontraron usuarios"
                        description="Prueba con otro nombre o correo."
                    />
                ) : (
                    <ul className="divide-y overflow-hidden rounded-xl border bg-card">
                        {list.data.map((user) => (
                            <li
                                key={user.id}
                                className="flex flex-col gap-3 p-4 md:flex-row md:items-center"
                            >
                                <div className="min-w-0 flex-1 space-y-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="truncate font-medium">
                                            {user.name}
                                        </p>
                                        {user.is_self && (
                                            <Badge variant="outline">Tú</Badge>
                                        )}
                                        {user.role.value === 'admin' && (
                                            <Badge>
                                                <ShieldCheck aria-hidden />
                                                Administrador
                                            </Badge>
                                        )}
                                        {!user.is_active && (
                                            <Badge variant="destructive">
                                                Desactivada
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="truncate text-sm text-muted-foreground">
                                        {user.email}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {user.community
                                            ? `${user.community} · `
                                            : ''}
                                        {pluralize(
                                            user.publications_count,
                                            'publicación',
                                            'publicaciones',
                                        )}{' '}
                                        · Desde{' '}
                                        {formatMonthYear(user.created_at)}
                                    </p>
                                </div>

                                <div className="flex flex-wrap items-center gap-2">
                                    <label
                                        htmlFor={`role-${user.id}`}
                                        className="sr-only"
                                    >
                                        Rol de {user.name}
                                    </label>
                                    <Select
                                        value={user.role.value}
                                        disabled={user.is_self}
                                        onValueChange={(value) => {
                                            const role = roles.find(
                                                (r) => r.value === value,
                                            );

                                            if (
                                                role &&
                                                role.value !== user.role.value
                                            ) {
                                                setPending({
                                                    kind: 'role',
                                                    user,
                                                    role,
                                                });
                                            }
                                        }}
                                    >
                                        <SelectTrigger
                                            id={`role-${user.id}`}
                                            className="w-40"
                                        >
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roles.map((role) => (
                                                <SelectItem
                                                    key={role.value}
                                                    value={role.value}
                                                >
                                                    {role.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <Button
                                        variant={
                                            user.is_active
                                                ? 'outline'
                                                : 'default'
                                        }
                                        disabled={user.is_self}
                                        onClick={() =>
                                            setPending({
                                                kind: 'status',
                                                user,
                                                active: !user.is_active,
                                            })
                                        }
                                        title={
                                            user.is_self
                                                ? 'No puedes desactivar tu propia cuenta'
                                                : undefined
                                        }
                                    >
                                        {user.is_active
                                            ? 'Desactivar'
                                            : 'Activar'}
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                <PaginationNav
                    className="mt-6"
                    currentPage={list.meta.current_page}
                    lastPage={list.meta.last_page}
                    href={(page) =>
                        usersIndex({
                            query: {
                                ...(filters.q ? { q: filters.q } : {}),
                                page,
                            },
                        })
                    }
                />
            </div>

            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                processing={processing}
                destructive={
                    pending?.kind === 'status' && pending.active === false
                }
                title={
                    pending?.kind === 'status'
                        ? pending.active
                            ? '¿Activar esta cuenta?'
                            : '¿Desactivar esta cuenta?'
                        : '¿Cambiar el rol?'
                }
                description={
                    pending?.kind === 'status'
                        ? pending.active
                            ? `${pending.user.name} podrá volver a iniciar sesión.`
                            : `${pending.user.name} no podrá iniciar sesión y su sesión actual se cerrará.`
                        : pending
                          ? `${pending.user.name} pasará a tener el rol de ${pending.role.label.toLowerCase()}.`
                          : ''
                }
                confirmLabel={
                    pending?.kind === 'status'
                        ? pending.active
                            ? 'Activar'
                            : 'Desactivar'
                        : 'Cambiar rol'
                }
                onConfirm={confirm}
            />
        </>
    );
}

AdminUsers.layout = {
    breadcrumbs: [
        { title: 'Administración', href: dashboard() },
        { title: 'Usuarios', href: usersIndex() },
    ],
};
