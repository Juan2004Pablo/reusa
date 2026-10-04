import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';

export default function Welcome() {
    return (
        <>
            <Head title="Dona, intercambia y vende lo que ya no usas" />
            <section className="mx-auto flex w-full max-w-4xl flex-col items-center gap-6 px-4 py-24 text-center sm:px-6">
                <h1 className="text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                    Dale una segunda vida a lo que ya no usas
                </h1>
                <p className="max-w-2xl text-lg text-muted-foreground">
                    ReUsa conecta a vecinos de Medellín para donar, intercambiar
                    o vender objetos en desuso. Menos residuos, más comunidad.
                </p>
                <div className="flex flex-wrap justify-center gap-3">
                    <Button size="lg" asChild>
                        <Link href={register()}>Crear cuenta</Link>
                    </Button>
                    <Button size="lg" variant="outline" asChild>
                        <Link href={login()}>Iniciar sesión</Link>
                    </Button>
                </div>
            </section>
        </>
    );
}
