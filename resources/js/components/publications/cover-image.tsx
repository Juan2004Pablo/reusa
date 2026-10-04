import { Tag } from 'lucide-react';
import { cn } from '@/lib/utils';

/** Foto de portada; sin foto muestra una etiqueta vacía sobre papel. */
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
                'relative overflow-hidden bg-paper',
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
                    className="flex size-full flex-col items-center justify-center gap-2 text-muted-foreground"
                >
                    <Tag
                        className="size-7 -rotate-12"
                        strokeWidth={1.5}
                        aria-hidden="true"
                    />
                    <span className="type-label">Sin foto</span>
                </div>
            )}
        </div>
    );
}
