import { Link } from '@inertiajs/react';
import ModalityLabel from '@/components/market/modality-label';
import PriceTag from '@/components/market/price-tag';
import StatusStamp from '@/components/market/status-stamp';
import { formatRelative } from '@/lib/format';
import { neighborhood } from '@/lib/location';
import { cn } from '@/lib/utils';
import { show } from '@/routes/publications';
import type { PublicationCard as PublicationCardType } from '@/types';
import CoverImage from './cover-image';

/**
 * Objeto en el catálogo: foto, sello si ya no está disponible, modalidad, título,
 * etiqueta de precio y «BARRIO · HACE X». Sin caja ni sombra: la foto es el objeto.
 */
export default function PublicationCard({
    publication,
}: {
    publication: PublicationCardType;
}) {
    const unavailable = publication.status.value !== 'available';

    return (
        <article className="group relative flex h-full flex-col gap-3 rounded-md focus-within:ring-[3px] focus-within:ring-ring/60 focus-within:ring-offset-4 focus-within:ring-offset-background">
            <div className="relative overflow-hidden rounded-md">
                <CoverImage
                    src={publication.cover_url}
                    alt={publication.title}
                    imgClassName={cn(
                        'transition-transform duration-500 ease-out group-hover:scale-[1.03]',
                        unavailable && 'saturate-[0.35]',
                    )}
                />
                {unavailable && (
                    <StatusStamp
                        status={publication.status.value}
                        label={publication.status.label}
                        tilt
                        className="absolute top-4 right-4"
                    />
                )}
            </div>

            <div className="flex flex-1 flex-col gap-2">
                <div className="flex items-center justify-between gap-3">
                    <ModalityLabel
                        modality={publication.modality.value}
                        label={publication.modality.label}
                    />
                    <span className="type-label truncate text-muted-foreground">
                        {publication.category.name}
                    </span>
                </div>

                <h3 className="line-clamp-2 text-[1.05rem] leading-snug font-semibold">
                    <Link
                        href={show(publication.slug)}
                        prefetch
                        className="decoration-2 underline-offset-4 group-hover:underline after:absolute after:inset-0 after:content-[''] focus-visible:outline-none"
                    >
                        {publication.title}
                    </Link>
                </h3>

                <div className="mt-auto flex items-end justify-between gap-3 pt-1">
                    <PriceTag
                        modality={publication.modality.value}
                        price={publication.price}
                    />
                    <p
                        className="type-label text-right text-muted-foreground"
                        title={publication.location}
                    >
                        <span className="block text-foreground">
                            {neighborhood(publication.location)}
                        </span>
                        {publication.created_at && (
                            <time dateTime={publication.created_at}>
                                {formatRelative(publication.created_at)}
                            </time>
                        )}
                    </p>
                </div>
            </div>
        </article>
    );
}

export function PublicationCardSkeleton() {
    return (
        <div className="flex flex-col gap-3" aria-hidden="true">
            <div className="aspect-[4/3] animate-pulse rounded-md bg-paper" />
            <div className="h-3 w-1/3 animate-pulse rounded-sm bg-muted" />
            <div className="h-4 w-4/5 animate-pulse rounded-sm bg-muted" />
            <div className="flex justify-between">
                <div className="h-7 w-24 animate-pulse rounded-sm bg-muted" />
                <div className="h-7 w-16 animate-pulse rounded-sm bg-muted" />
            </div>
        </div>
    );
}
