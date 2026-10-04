import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

/** Logotipo: etiqueta colgante + «ReUsa» en Archivo ancha. */
export default function Brand({ className }: { className?: string }) {
    return (
        <Link
            href={home()}
            className={cn(
                'inline-flex items-center gap-1.5 rounded-sm text-foreground focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none',
                className,
            )}
            aria-label="ReUsa, ir al inicio"
        >
            <AppLogoIcon className="size-8 fill-primary text-primary" />
            <span className="type-display text-xl">
                Re<span className="text-primary">Usa</span>
            </span>
        </Link>
    );
}
