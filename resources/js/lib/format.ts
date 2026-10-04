const cop = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
});

const integer = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

const relative = new Intl.RelativeTimeFormat('es-CO', { numeric: 'auto' });

const longDate = new Intl.DateTimeFormat('es-CO', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const monthYear = new Intl.DateTimeFormat('es-CO', {
    month: 'long',
    year: 'numeric',
});

/** 120000 → "$ 120.000" */
export function formatCOP(value: number): string {
    return cop.format(value).replace(/\s/g, ' ');
}

/** 1500000 → "1.500.000" (para campos de texto sin símbolo). */
export function formatInteger(value: number | string): string {
    const number = typeof value === 'string' ? Number(value) : value;

    return Number.isFinite(number) ? integer.format(number) : '';
}

const UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
    ['year', 365 * 24 * 3600],
    ['month', 30 * 24 * 3600],
    ['week', 7 * 24 * 3600],
    ['day', 24 * 3600],
    ['hour', 3600],
    ['minute', 60],
];

/** "hace 3 días", "ayer", "hace un momento". */
export function formatRelative(iso: string | null, now = Date.now()): string {
    if (!iso) {
        return '';
    }

    const seconds = Math.round((new Date(iso).getTime() - now) / 1000);

    for (const [unit, size] of UNITS) {
        if (Math.abs(seconds) >= size) {
            return relative.format(Math.round(seconds / size), unit);
        }
    }

    return 'hace un momento';
}

/** "4 de octubre de 2026" */
export function formatDate(iso: string | null): string {
    return iso ? longDate.format(new Date(iso)) : '';
}

/** "octubre de 2026" */
export function formatMonthYear(iso: string | null): string {
    return iso ? monthYear.format(new Date(iso)) : '';
}

/** Singular/plural en español: pluralize(3, 'objeto', 'objetos') → "3 objetos". */
export function pluralize(count: number, one: string, many: string): string {
    return `${integer.format(count)} ${count === 1 ? one : many}`;
}
