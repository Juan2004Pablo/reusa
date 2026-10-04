import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { PublicationImage } from '@/types';
import CoverImage from './cover-image';

/** Galería con miniaturas; se navega con los botones o con las flechas del teclado. */
export default function Gallery({
    images,
    title,
}: {
    images: PublicationImage[];
    title: string;
}) {
    const [current, setCurrent] = useState(0);

    if (images.length === 0) {
        return (
            <CoverImage
                src={null}
                alt={title}
                className="aspect-[4/3] rounded-xl"
            />
        );
    }

    const total = images.length;
    const go = (index: number) => setCurrent((index + total) % total);

    return (
        <div
            role="group"
            aria-roledescription="galería"
            aria-label={`Fotografías de ${title}`}
            className="space-y-3"
            tabIndex={0}
            onKeyDown={(event) => {
                if (event.key === 'ArrowLeft') {
                    go(current - 1);
                } else if (event.key === 'ArrowRight') {
                    go(current + 1);
                }
            }}
        >
            <div className="relative overflow-hidden rounded-md bg-paper">
                <CoverImage
                    src={images[current].url}
                    alt={`${title} (foto ${current + 1} de ${total})`}
                    className="aspect-[4/3]"
                />
                {total > 1 && (
                    <>
                        <Button
                            type="button"
                            variant="secondary"
                            size="icon"
                            className="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-background/90 shadow-sm"
                            onClick={() => go(current - 1)}
                            aria-label="Foto anterior"
                        >
                            <ChevronLeft aria-hidden />
                        </Button>
                        <Button
                            type="button"
                            variant="secondary"
                            size="icon"
                            className="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-background/90 shadow-sm"
                            onClick={() => go(current + 1)}
                            aria-label="Foto siguiente"
                        >
                            <ChevronRight aria-hidden />
                        </Button>
                        <span
                            className="absolute right-3 bottom-3 rounded-sm bg-background/90 px-2 py-0.5 font-mono text-xs font-medium tabular-nums"
                            aria-hidden
                        >
                            {current + 1} / {total}
                        </span>
                    </>
                )}
            </div>

            {total > 1 && (
                <ul className="grid grid-cols-4 gap-2">
                    {images.map((image, index) => (
                        <li key={image.id}>
                            <button
                                type="button"
                                onClick={() => setCurrent(index)}
                                aria-label={`Ver foto ${index + 1} de ${total}`}
                                aria-current={index === current}
                                className={cn(
                                    'block w-full overflow-hidden rounded-sm border-2 transition-opacity focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
                                    index === current
                                        ? 'border-foreground'
                                        : 'border-transparent opacity-70 hover:opacity-100',
                                )}
                            >
                                <img
                                    src={image.url}
                                    alt=""
                                    loading="lazy"
                                    className="aspect-square w-full object-cover"
                                />
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
