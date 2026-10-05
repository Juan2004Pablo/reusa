import { Link } from '@inertiajs/react';
import Brand from '@/components/brand';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh lg:grid-cols-[1fr_1.1fr]">
            <aside className="surface-primary hidden flex-col justify-between p-12 text-primary-foreground lg:flex">
                <Brand className="text-primary-foreground [&_span]:!text-primary-foreground [&_svg]:fill-primary-foreground" />
                <div className="space-y-5">
                    <p className="type-display text-5xl">
                        Dale una segunda vida a lo que ya no usas.
                    </p>
                    <p className="max-w-sm text-primary-foreground/80">
                        Dona, intercambia o vende dentro de tu comunidad en
                        Medellín.
                    </p>
                </div>
                <p className="type-label text-primary-foreground/70">
                    Proyecto Integrador · ODS 12
                </p>
            </aside>

            <main className="flex flex-col items-center justify-center gap-8 p-6 md:p-10">
                <Brand className="lg:hidden" />
                <div className="w-full max-w-sm space-y-8">
                    <div className="space-y-2">
                        <h1 className="type-display text-3xl">{title}</h1>
                        <p className="text-muted-foreground">{description}</p>
                    </div>
                    {children}
                    <p className="type-label text-center text-muted-foreground">
                        <Link href="/terms" className="hover:underline">
                            Términos y condiciones
                        </Link>
                    </p>
                </div>
            </main>
        </div>
    );
}
