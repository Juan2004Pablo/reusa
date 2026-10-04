import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/** `true` mientras Inertia está cargando una nueva página o filtro. */
export function useNavigating(): boolean {
    const [navigating, setNavigating] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', () => setNavigating(true));
        const offFinish = router.on('finish', () => setNavigating(false));

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    return navigating;
}
