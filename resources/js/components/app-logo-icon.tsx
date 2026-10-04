import type { SVGAttributes } from 'react';

/** Marca de ReUsa: una etiqueta colgante de mercado, con su ojal y su cordón. */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 32 32"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <g transform="rotate(-14 16 17)">
                <path
                    fillRule="evenodd"
                    d="M10.2 9.4 16 3.8l5.8 5.6c.4.4.7 1 .7 1.6V27a2 2 0 0 1-2 2h-9a2 2 0 0 1-2-2V11c0-.6.3-1.2.7-1.6ZM16 11.6a2.1 2.1 0 1 0 0-4.2 2.1 2.1 0 0 0 0 4.2Z"
                />
            </g>
            <path
                d="M14.6 7.6C11 5.4 7.4 3.9 3 4.6"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
            />
        </svg>
    );
}
