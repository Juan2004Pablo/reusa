import { cn } from '@/lib/utils';
import type { Modality } from '@/types';

const dot: Record<Modality, string> = {
    donation: 'bg-donation',
    exchange: 'bg-exchange',
    sale: 'bg-sale',
};

/**
 * Modalidad como rótulo: punto de color + palabra. El texto va en tinta, nunca en el
 * color de la modalidad, así se lee igual con cualquier visión de color.
 */
export default function ModalityLabel({
    modality,
    label,
    className,
}: {
    modality: Modality;
    label: string;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'type-label inline-flex items-center gap-1.5 text-foreground',
                className,
            )}
        >
            <span
                className={cn('size-2 shrink-0 rounded-full', dot[modality])}
                aria-hidden
            />
            {label}
        </span>
    );
}
