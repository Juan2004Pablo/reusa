import { formatCOP } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Modality } from '@/types';

/**
 * Etiqueta colgante de kraft. En una venta muestra el precio en COP; en una donación
 * «Gratis» y en un intercambio «Intercambio». Es el único lugar donde aparece el kraft.
 */
export default function PriceTag({
    modality,
    price,
    size = 'md',
    className,
}: {
    modality: Modality;
    price: number | null;
    size?: 'md' | 'lg';
    className?: string;
}) {
    const isPrice = modality === 'sale' && price !== null;
    const text = isPrice
        ? formatCOP(price)
        : modality === 'donation'
          ? 'Gratis'
          : 'Intercambio';

    return (
        <span
            className={cn('price-tag', className)}
            data-size={size}
            data-kind={isPrice ? 'price' : 'word'}
        >
            {isPrice && <span className="sr-only">Precio: </span>}
            {text}
        </span>
    );
}
