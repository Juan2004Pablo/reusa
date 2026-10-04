import { ImageOff } from 'lucide-react';
import { cn } from '@/lib/utils';

/** Imagen de portada con marcador de posición cuando la publicación no tiene fotos. */
export default function CoverImage({
    src,
    alt,
    className,
    imgClassName,
}: {
    src: string | null;
    alt: string;
    className?: string;
    imgClassName?: string;
}) {
    return (
        <div
            className={cn(
                'relative overflow-hidden bg-muted',
                className ?? 'aspect-[4/3]',
            )}
        >
            {src ? (
                <img
                    src={src}
                    alt={alt}
                    loading="lazy"
                    decoding="async"
                    className={cn('size-full object-cover', imgClassName)}
                />
            ) : (
                <div
                    role="img"
                    aria-label="Sin fotografía"
                    className="flex size-full flex-col items-center justify-center gap-1 bg-gradient-to-br from-accent to-muted text-muted-foreground"
                >
                    <ImageOff className="size-8" aria-hidden="true" />
                    <span className="text-xs">Sin fotografía</span>
                </div>
            )}
        </div>
    );
}
