import { Head, Link } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { register } from '@/routes';

const prohibitedItems = [
    'Medicamentos',
    'Sustancias controladas',
    'Armas',
    'Artículos de carácter sexual',
    'Productos de aseo e higiene personal usados',
    'Productos perecederos',
];

export default function Terms() {
    return (
        <>
            <Head title="Términos y condiciones" />

            <article className="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6">
                <header className="mb-8 space-y-2">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Términos y condiciones
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Última actualización: octubre de 2026
                    </p>
                </header>

                <Alert className="mb-8 border-primary/30 bg-accent">
                    <AlertTriangle />
                    <AlertTitle>En pocas palabras</AlertTitle>
                    <AlertDescription>
                        ReUsa es solo un canal de contacto entre personas de la
                        comunidad. No participamos en la negociación, el pago,
                        la entrega ni la verificación de los objetos.
                    </AlertDescription>
                </Alert>

                <div className="space-y-8 leading-relaxed text-foreground/90 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-foreground [&_li]:ml-5 [&_li]:list-disc [&_p+p]:mt-3">
                    <section aria-labelledby="que-es">
                        <h2 id="que-es">1. Qué es ReUsa</h2>
                        <p>
                            ReUsa es una plataforma web de economía circular
                            comunitaria, desarrollada como proyecto académico
                            para una comunidad piloto en Medellín. Permite
                            publicar objetos en desuso para donarlos,
                            intercambiarlos o venderlos, y que otros miembros de
                            la comunidad los encuentren.
                        </p>
                    </section>

                    <section aria-labelledby="rol">
                        <h2 id="rol">2. Rol de ReUsa: únicamente un canal</h2>
                        <p>
                            ReUsa actúa únicamente como canal de contacto entre
                            usuarios. ReUsa no interviene en la negociación, el
                            pago, la entrega ni la verificación de los objetos
                            publicados. Cuando una publicación muestra un
                            precio, este es solo informativo: la plataforma no
                            procesa pagos ni gestiona dinero.
                        </p>
                        <p>
                            Cualquier acuerdo sobre el valor, la forma de pago,
                            el lugar y el momento de la entrega se coordina
                            directamente entre las partes y bajo su
                            responsabilidad.
                        </p>
                    </section>

                    <section aria-labelledby="responsabilidad">
                        <h2 id="responsabilidad">
                            3. Limitación de responsabilidad
                        </h2>
                        <p>
                            El equipo desarrollador de ReUsa no asume
                            responsabilidad por:
                        </p>
                        <ul>
                            <li>
                                El estado, la calidad, la procedencia o la
                                legalidad de los objetos publicados.
                            </li>
                            <li>
                                Los acuerdos económicos o de entrega entre las
                                partes.
                            </li>
                            <li>
                                Los daños, pérdidas o disputas que puedan
                                derivarse de una transacción entre usuarios.
                            </li>
                        </ul>
                        <p>
                            Te recomendamos revisar el objeto antes de cerrar un
                            acuerdo y coordinar los encuentros en lugares
                            seguros y concurridos.
                        </p>
                    </section>

                    <section aria-labelledby="no-admitidos">
                        <h2 id="no-admitidos">4. Productos no admitidos</h2>
                        <p>No está permitido publicar:</p>
                        <ul>
                            {prohibitedItems.map((item) => (
                                <li key={item}>{item}</li>
                            ))}
                        </ul>
                        <p>
                            La administración puede ocultar o retirar cualquier
                            publicación que incumpla estas reglas.
                        </p>
                    </section>

                    <section aria-labelledby="cuenta">
                        <h2 id="cuenta">5. Tu cuenta y tu conducta</h2>
                        <ul>
                            <li>
                                Debes entregar información veraz y mantener
                                segura tu contraseña.
                            </li>
                            <li>
                                Eres responsable del contenido que publicas
                                (textos y fotografías) y declaras tener derecho
                                a compartirlo.
                            </li>
                            <li>
                                Trata con respeto a los demás miembros de la
                                comunidad.
                            </li>
                            <li>
                                La administración puede desactivar cuentas que
                                incumplan estos términos.
                            </li>
                        </ul>
                    </section>

                    <section aria-labelledby="datos">
                        <h2 id="datos">6. Tus datos</h2>
                        <p>
                            Usamos los datos que registras (nombre, correo y, si
                            los indicas, teléfono y comunidad) únicamente para
                            el funcionamiento de la plataforma. No los vendemos.
                            Puedes actualizarlos o eliminar tu cuenta en
                            cualquier momento desde tu perfil.
                        </p>
                    </section>

                    <section aria-labelledby="cambios">
                        <h2 id="cambios">7. Cambios en estos términos</h2>
                        <p>
                            ReUsa es un prototipo en evolución. Estos términos
                            pueden cambiar; la fecha de la última actualización
                            aparece al inicio de esta página.
                        </p>
                    </section>
                </div>

                <p className="mt-10 text-sm text-muted-foreground">
                    ¿Aún no tienes cuenta?{' '}
                    <Link
                        href={register()}
                        className="font-medium text-foreground underline underline-offset-4"
                    >
                        Crea una
                    </Link>
                    .
                </p>
            </article>
        </>
    );
}
