import {
    Blocks,
    Bike,
    BookOpen,
    CookingPot,
    Laptop,
    Package,
    Shirt,
    Sofa,
    Wrench,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const icons: Record<string, LucideIcon> = {
    shirt: Shirt,
    'book-open': BookOpen,
    sofa: Sofa,
    'cooking-pot': CookingPot,
    laptop: Laptop,
    wrench: Wrench,
    bike: Bike,
    blocks: Blocks,
};

/** Icono de una macrocategoría a partir del nombre guardado en la base de datos. */
export default function CategoryIcon({
    name,
    className,
}: {
    name: string | null;
    className?: string;
}) {
    const Icon = (name && icons[name]) || Package;

    return <Icon className={className} aria-hidden="true" />;
}
