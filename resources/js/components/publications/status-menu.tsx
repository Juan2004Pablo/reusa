import { router } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import type { ComponentProps } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { update } from '@/routes/publications/status';
import type { Option } from '@/types';

/** Menú con solo los cambios de estado válidos para la publicación. */
export default function StatusMenu({
    slug,
    options,
    size = 'default',
    variant = 'outline',
}: {
    slug: string;
    options: Option[];
    size?: ComponentProps<typeof Button>['size'];
    variant?: ComponentProps<typeof Button>['variant'];
}) {
    if (options.length === 0) {
        return (
            <Button variant={variant} size={size} disabled>
                Estado final
            </Button>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant={variant} size={size}>
                    Cambiar estado
                    <ChevronDown aria-hidden />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-48">
                <DropdownMenuLabel>Marcar como…</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {options.map((option) => (
                    <DropdownMenuItem
                        key={option.value}
                        onSelect={() =>
                            router.patch(
                                update.url(slug),
                                { status: option.value },
                                { preserveScroll: true },
                            )
                        }
                    >
                        {option.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
