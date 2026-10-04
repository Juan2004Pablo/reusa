import { Gift, HandCoins, Repeat } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { Modality } from '@/types';

const styles: Record<Modality, { className: string; icon: LucideIcon }> = {
    donation: {
        className: 'border-transparent bg-donation text-donation-foreground',
        icon: Gift,
    },
    exchange: {
        className: 'border-transparent bg-exchange text-exchange-foreground',
        icon: Repeat,
    },
    sale: {
        className: 'border-transparent bg-sale text-sale-foreground',
        icon: HandCoins,
    },
};

export default function ModalityBadge({
    modality,
    label,
    className,
}: {
    modality: Modality;
    label: string;
    className?: string;
}) {
    const { className: color, icon: Icon } = styles[modality];

    return (
        <Badge className={cn(color, className)}>
            <Icon aria-hidden="true" />
            {label}
        </Badge>
    );
}
