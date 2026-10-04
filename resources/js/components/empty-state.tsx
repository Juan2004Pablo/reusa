import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

export default function EmptyState({
    icon: Icon,
    title,
    description,
    children,
}: {
    icon: LucideIcon;
    title: string;
    description: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed bg-card/50 px-6 py-14 text-center">
            <span className="flex size-14 items-center justify-center rounded-full bg-accent text-accent-foreground">
                <Icon className="size-7" aria-hidden />
            </span>
            <h2 className="text-lg font-semibold">{title}</h2>
            <p className="max-w-md text-sm text-muted-foreground">
                {description}
            </p>
            {children && (
                <div className="mt-2 flex flex-wrap justify-center gap-2">
                    {children}
                </div>
            )}
        </div>
    );
}
