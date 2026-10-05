import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

/**
 * Frase que cambia sola cada cierto tiempo: la actual sube y se desvanece, la siguiente entra
 * desde abajo. Todas las frases ocupan la misma celda, así la altura la fija la más larga y la
 * página no salta. Se detiene al pasar el cursor o enfocar dentro, y no rota si la persona
 * pidió menos movimiento (queda la primera frase fija).
 */
export default function RotatingPhrase({
    phrases,
    interval = 5500,
    as: Tag = 'div',
    className,
}: {
    phrases: string[];
    interval?: number;
    as?: 'div' | 'h1' | 'h2' | 'p';
    className?: string;
}) {
    const [active, setActive] = useState(0);
    const [previous, setPrevious] = useState<number | null>(null);
    const [paused, setPaused] = useState(false);
    const [reducedMotion, setReducedMotion] = useState(true);

    useEffect(() => {
        const query = window.matchMedia('(prefers-reduced-motion: reduce)');
        const sync = () => setReducedMotion(query.matches);

        sync();
        query.addEventListener('change', sync);

        return () => query.removeEventListener('change', sync);
    }, []);

    useEffect(() => {
        if (paused || reducedMotion || phrases.length < 2) {
            return;
        }

        const timer = setTimeout(() => {
            setPrevious(active);
            setActive((active + 1) % phrases.length);
        }, interval);

        return () => clearTimeout(timer);
    }, [active, paused, reducedMotion, phrases.length, interval]);

    return (
        <Tag
            className={cn('grid', className)}
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
        >
            {phrases.map((phrase, i) => (
                <span
                    key={phrase}
                    aria-hidden={i !== active}
                    className={cn(
                        'col-start-1 row-start-1 block transition-[opacity,translate] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none',
                        i === active && 'translate-y-0 opacity-100',
                        i === previous && '-translate-y-4 opacity-0',
                        i !== active &&
                            i !== previous &&
                            'translate-y-4 opacity-0 transition-none',
                    )}
                >
                    {phrase}
                </span>
            ))}
        </Tag>
    );
}
