import { ArrowLeft, ArrowRight, ImagePlus, X } from 'lucide-react';
import { useId, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export const MAX_IMAGES = 4;
export const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
export const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

export type ImageItem =
    | { key: string; kind: 'existing'; id: number; url: string }
    | { key: string; kind: 'new'; file: File; url: string };

type Props = {
    items: ImageItem[];
    onChange: (items: ImageItem[]) => void;
    /** Mensajes de error del servidor para las fotografías. */
    errors?: string[];
};

/**
 * Selector de fotografías (1 a 4) con previsualización, orden y eliminación.
 * La primera fotografía es la portada. Valida tipo y tamaño antes de subir nada.
 */
export default function ImageUploader({ items, onChange, errors = [] }: Props) {
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [notices, setNotices] = useState<string[]>([]);
    const remaining = MAX_IMAGES - items.length;

    const addFiles = (list: FileList | null) => {
        if (!list) {
            return;
        }

        const problems: string[] = [];
        const accepted: ImageItem[] = [];

        Array.from(list).forEach((file) => {
            if (!ACCEPTED_TYPES.includes(file.type)) {
                problems.push(
                    `«${file.name}»: solo se permiten JPG, PNG o WEBP.`,
                );
            } else if (file.size > MAX_IMAGE_BYTES) {
                problems.push(`«${file.name}»: supera el máximo de 2 MB.`);
            } else if (accepted.length >= remaining) {
                problems.push(
                    `«${file.name}»: ya alcanzaste el máximo de ${MAX_IMAGES} fotografías.`,
                );
            } else {
                accepted.push({
                    key: crypto.randomUUID(),
                    kind: 'new',
                    file,
                    url: URL.createObjectURL(file),
                });
            }
        });

        setNotices(problems);

        if (accepted.length > 0) {
            onChange([...items, ...accepted]);
        }

        // Permite volver a elegir el mismo archivo.
        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };

    const remove = (index: number) => {
        const item = items[index];

        if (item.kind === 'new') {
            URL.revokeObjectURL(item.url);
        }

        setNotices([]);
        onChange(items.filter((_, i) => i !== index));
    };

    const move = (index: number, delta: -1 | 1) => {
        const target = index + delta;

        if (target < 0 || target >= items.length) {
            return;
        }

        const next = [...items];
        [next[index], next[target]] = [next[target], next[index]];
        onChange(next);
    };

    const allErrors = [...new Set([...errors, ...notices])];

    return (
        <div className="space-y-4">
            {items.length > 0 && (
                <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {items.map((item, index) => (
                        <li
                            key={item.key}
                            className="group relative overflow-hidden rounded-lg border bg-muted"
                        >
                            <img
                                src={item.url}
                                alt={`Previsualización de la fotografía ${index + 1}`}
                                className="aspect-square w-full object-cover"
                            />
                            {index === 0 && (
                                <span className="absolute top-2 left-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground">
                                    Portada
                                </span>
                            )}
                            <Button
                                type="button"
                                size="icon"
                                variant="secondary"
                                className="absolute top-1.5 right-1.5 size-7 rounded-full shadow"
                                onClick={() => remove(index)}
                                aria-label={`Quitar fotografía ${index + 1}`}
                            >
                                <X aria-hidden />
                            </Button>
                            <div className="absolute inset-x-0 bottom-0 flex justify-between bg-gradient-to-t from-black/50 to-transparent p-1.5">
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="secondary"
                                    className="size-7 rounded-full"
                                    disabled={index === 0}
                                    onClick={() => move(index, -1)}
                                    aria-label={`Mover fotografía ${index + 1} a la izquierda`}
                                >
                                    <ArrowLeft aria-hidden />
                                </Button>
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="secondary"
                                    className="size-7 rounded-full"
                                    disabled={index === items.length - 1}
                                    onClick={() => move(index, 1)}
                                    aria-label={`Mover fotografía ${index + 1} a la derecha`}
                                >
                                    <ArrowRight aria-hidden />
                                </Button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <div>
                <input
                    ref={inputRef}
                    id={inputId}
                    type="file"
                    accept={ACCEPTED_TYPES.join(',')}
                    multiple
                    className="peer sr-only"
                    disabled={remaining <= 0}
                    onChange={(event) => addFiles(event.target.files)}
                />
                <label
                    htmlFor={inputId}
                    className={cn(
                        'flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed px-4 py-8 text-center transition-colors peer-focus-visible:ring-[3px] peer-focus-visible:ring-ring/50 hover:bg-accent/50',
                        remaining <= 0 &&
                            'cursor-not-allowed opacity-60 hover:bg-transparent',
                        allErrors.length > 0 && 'border-destructive',
                    )}
                >
                    <ImagePlus
                        className="size-7 text-muted-foreground"
                        aria-hidden
                    />
                    <span className="font-medium">
                        {remaining > 0
                            ? items.length === 0
                                ? 'Agregar fotografías'
                                : 'Agregar más fotografías'
                            : 'Llegaste al máximo de fotografías'}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        JPG, PNG o WEBP · máximo 2 MB cada una · de 1 a{' '}
                        {MAX_IMAGES} fotos ({items.length}/{MAX_IMAGES})
                    </span>
                </label>
            </div>

            {allErrors.length > 0 && (
                <ul
                    role="alert"
                    className="space-y-1 text-sm text-red-600 dark:text-red-400"
                >
                    {allErrors.map((message) => (
                        <li key={message}>{message}</li>
                    ))}
                </ul>
            )}
        </div>
    );
}
