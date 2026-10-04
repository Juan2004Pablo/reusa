import { cn } from '@/lib/utils';
import type { PublicationStatusValue } from '@/types';

/**
 * Sello de caucho con el estado. Lleva siempre la palabra (nunca solo color):
 * reservado en tinta azul; entregado y vendido en tinta roja.
 */
export default function StatusStamp({
    status,
    label,
    tilt = false,
    size = 'md',
    className,
}: {
    status: PublicationStatusValue;
    label: string;
    tilt?: boolean;
    size?: 'md' | 'lg';
    className?: string;
}) {
    return (
        <span
            className={cn('status-stamp', className)}
            data-status={status}
            data-tilt={tilt}
            data-size={size}
        >
            {label}
        </span>
    );
}
