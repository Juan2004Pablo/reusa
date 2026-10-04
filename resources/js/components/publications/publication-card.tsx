import { Link } from '@inertiajs/react';
import { Clock, MapPin } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';
import { show } from '@/routes/publications';
import type { PublicationCard as PublicationCardType } from '@/types';
import CoverImage from './cover-image';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import StatusStamp from '@/components/market/status-stamp';

export default function PublicationCard({
    publication,
}: {
    publication: PublicationCardType;
}) {
    const unavailable = publication.status.value !== 'available';

    return (
        <Card className="group relative gap-0 overflow-hidden py-0 transition-shadow focus-within:ring-[3px] focus-within:ring-ring/50 hover:shadow-md">
            <div className="relative">
                <CoverImage
                    src={publication.cover_url}
                    alt={publication.title}
                    imgClassName={cn(
                        'transition-transform duration-300 group-hover:scale-[1.03]',
                        unavailable && 'opacity-70 grayscale',
                    )}
                />
                <ModalityLabel
                    modality={publication.modality.value}
                    label={publication.modality.label}
                    className="absolute top-3 left-3 shadow-sm"
                />
                {unavailable && (
                    <StatusStamp
                        status={publication.status.value}
                        label={publication.status.label}
                        className="absolute top-3 right-3 shadow-sm"
                    />
                )}
            </div>

            <div className="flex flex-1 flex-col gap-2 p-4">
                <p className="truncate text-xs text-muted-foreground">
                    {publication.category.name}
                </p>
                <h3 className="line-clamp-2 min-h-[2.5rem] leading-snug font-medium">
                    <Link
                        href={show(publication.slug)}
                        prefetch
                        className="after:absolute after:inset-0 after:content-[''] focus-visible:outline-none"
                    >
                        {publication.title}
                    </Link>
                </h3>
                <PriceTag
                    modality={publication.modality.value}
                    price={publication.price}
                    className="text-lg"
                />
                <div className="mt-auto flex items-center justify-between gap-2 pt-1 text-xs text-muted-foreground">
                    <span className="flex min-w-0 items-center gap-1">
                        <MapPin className="size-3.5 shrink-0" aria-hidden />
                        <span className="truncate">{publication.location}</span>
                    </span>
                    {publication.created_at && (
                        <time
                            dateTime={publication.created_at}
                            className="flex shrink-0 items-center gap-1"
                        >
                            <Clock className="size-3.5" aria-hidden />
                            {formatRelative(publication.created_at)}
                        </time>
                    )}
                </div>
            </div>
        </Card>
    );
}

export function PublicationCardSkeleton() {
    return (
        <div
            className="overflow-hidden rounded-xl border bg-card"
            aria-hidden="true"
        >
            <div className="aspect-[4/3] animate-pulse bg-muted" />
            <div className="space-y-3 p-4">
                <div className="h-3 w-1/3 animate-pulse rounded bg-muted" />
                <div className="h-4 w-4/5 animate-pulse rounded bg-muted" />
                <div className="h-5 w-1/4 animate-pulse rounded bg-muted" />
                <div className="h-3 w-2/3 animate-pulse rounded bg-muted" />
            </div>
        </div>
    );
}
