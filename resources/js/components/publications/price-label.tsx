import { formatCOP } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Modality } from '@/types';

/** Precio en COP para ventas; "Gratis" o "Intercambio" para el resto. */
export default function PriceLabel({
    modality,
    price,
    className,
}: {
    modality: Modality;
    price: number | null;
    className?: string;
}) {
    if (modality === 'sale' && price !== null) {
        return (
            <span className={cn('font-semibold tabular-nums', className)}>
                {formatCOP(price)}
            </span>
        );
    }

    return (
        <span className={cn('font-semibold', className)}>
            {modality === 'donation' ? 'Gratis' : 'Intercambio'}
        </span>
    );
}
