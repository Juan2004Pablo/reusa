import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

export default function Brand({ className }: { className?: string }) {
    return (
        <Link
            href={home()}
            className={cn(
                'inline-flex items-center gap-2 rounded-md font-semibold tracking-tight focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none',
                className,
            )}
            aria-label="ReUsa, ir al inicio"
        >
            <span className="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <AppLogoIcon className="size-5 fill-current" />
            </span>
            <span className="text-lg">ReUsa</span>
        </Link>
    );
}
