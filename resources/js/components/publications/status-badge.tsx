import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { PublicationStatusValue } from '@/types';

const styles: Record<PublicationStatusValue, string> = {
    available: 'border-transparent bg-primary text-primary-foreground',
    reserved: 'border-transparent bg-sale text-sale-foreground',
    delivered: 'border-transparent bg-secondary text-secondary-foreground',
    sold: 'border-transparent bg-secondary text-secondary-foreground',
};

export default function StatusBadge({
    status,
    label,
    className,
}: {
    status: PublicationStatusValue;
    label: string;
    className?: string;
}) {
    return <Badge className={cn(styles[status], className)}>{label}</Badge>;
}
