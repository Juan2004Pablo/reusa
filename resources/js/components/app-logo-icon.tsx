import type { SVGAttributes } from 'react';

/** Marca de ReUsa: una hoja que simboliza reutilizar y cuidar. */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 32 32"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <path d="M27 4C14 4 6 10.5 6 19.5 6 24.5 9.9 28.5 15 28.5 24.5 28.5 27 16 27 4ZM4 28.6l10-10 1.4 1.4-10 10Z" />
        </svg>
    );
}
