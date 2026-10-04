import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

/** Estado vacío: dice qué falta y cuál es el siguiente paso. */
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
        <div className="flex flex-col items-start gap-3 rounded-md border border-dashed border-input bg-paper/60 px-6 py-10 sm:px-10">
            <Icon
                className="size-8 -rotate-6 text-muted-foreground"
                strokeWidth={1.5}
                aria-hidden
            />
            <h2 className="type-display text-xl">{title}</h2>
            <p className="max-w-lg text-muted-foreground">{description}</p>
            {children && (
                <div className="mt-2 flex flex-wrap gap-2">{children}</div>
            )}
        </div>
    );
}
