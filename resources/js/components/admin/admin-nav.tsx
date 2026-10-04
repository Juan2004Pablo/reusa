import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { dashboard } from '@/routes/admin';
import { index as publicationsIndex } from '@/routes/admin/publications';
import { index as usersIndex } from '@/routes/admin/users';

const items = [
    { title: 'Resumen', href: dashboard(), exact: true },
    { title: 'Usuarios', href: usersIndex() },
    { title: 'Publicaciones', href: publicationsIndex() },
];

/** Pestañas de navegación del panel de administración. */
export default function AdminNav() {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <nav
            aria-label="Administración"
            className="mb-6 flex gap-1 overflow-x-auto border-b"
        >
            {items.map((item) => {
                const active = item.exact
                    ? isCurrentUrl(item.href)
                    : isCurrentOrParentUrl(item.href);

                return (
                    <Link
                        key={item.title}
                        href={item.href}
                        aria-current={active ? 'page' : undefined}
                        className={cn(
                            '-mb-px border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
                            active
                                ? 'border-primary text-foreground'
                                : 'border-transparent text-muted-foreground hover:text-foreground',
                        )}
                    >
                        {item.title}
                    </Link>
                );
            })}
        </nav>
    );
}
