import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/**
 * Encabezado de sección: rótulo en mono sobre un filete fino y titular ancho.
 * `index` solo se usa cuando el orden es información real (pasos, partes de un formulario).
 */
export default function SectionHeading({
    id,
    label,
    title,
    description,
    index,
    action,
    as: Tag = 'h2',
    size = 'lg',
    className,
}: {
    id?: string;
    label?: string;
    title: string;
    description?: string;
    index?: string;
    action?: ReactNode;
    as?: 'h1' | 'h2' | 'h3';
    size?: 'lg' | 'sm';
    className?: string;
}) {
    return (
        <header
            className={cn(
                'flex flex-wrap items-end justify-between gap-x-6 gap-y-3 border-t border-foreground/80 pt-3',
                className,
            )}
        >
            <div className="min-w-0 space-y-2">
                {(label || index) && (
                    <p className="type-label flex gap-3 text-muted-foreground">
                        {index && (
                            <span className="text-foreground">{index}</span>
                        )}
                        {label}
                    </p>
                )}
                <Tag
                    id={id}
                    className={cn(
                        'type-display',
                        size === 'lg'
                            ? 'text-2xl sm:text-3xl'
                            : 'text-xl sm:text-2xl',
                    )}
                >
                    {title}
                </Tag>
                {description && (
                    <p className="max-w-2xl text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            {action}
        </header>
    );
}
