import { Link, usePage } from '@inertiajs/react';
import { Menu, Plus } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import Brand from '@/components/brand';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { login, register, terms } from '@/routes';
import { index as myPublications } from '@/routes/my-publications';
import { create, index as catalog } from '@/routes/publications';
import type { NavItem } from '@/types';

/** Enlaces de la navegación principal del sitio público. */
const navItems: NavItem[] = [{ title: 'Catálogo', href: catalog() }];

export default function PublicLayout({ children }: PropsWithChildren) {
    const { auth } = usePage().props;
    const user = auth.user;

    return (
        <div className="flex min-h-svh flex-col bg-background text-foreground">
            <a
                href="#contenido"
                className="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded-md focus:bg-primary focus:px-3 focus:py-2 focus:text-primary-foreground"
            >
                Saltar al contenido
            </a>

            <header className="sticky top-0 z-40 border-b border-foreground/15 bg-background/90 backdrop-blur supports-[backdrop-filter]:bg-background/80">
                <div className="mx-auto flex h-16 w-full max-w-7xl items-center gap-4 px-4 sm:px-6">
                    <Brand />

                    <nav
                        aria-label="Principal"
                        className="ml-4 hidden items-center gap-1 md:flex"
                    >
                        {navItems.map((item) => (
                            <Button
                                key={item.title}
                                variant="ghost"
                                size="sm"
                                asChild
                            >
                                <Link href={item.href}>{item.title}</Link>
                            </Button>
                        ))}
                    </nav>

                    <div className="ml-auto hidden items-center gap-2 md:flex">
                        <Button size="sm" asChild>
                            <Link href={create()}>
                                <Plus aria-hidden />
                                Publicar objeto
                            </Link>
                        </Button>
                        {user ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        className="h-auto gap-2 px-2 py-1"
                                    >
                                        <UserInfo user={user} />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    className="min-w-56 rounded-lg"
                                    align="end"
                                >
                                    <UserMenuContent user={user} />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href={login()}>Iniciar sesión</Link>
                                </Button>
                                <Button size="sm" asChild>
                                    <Link href={register()}>Crear cuenta</Link>
                                </Button>
                            </>
                        )}
                    </div>

                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="ml-auto md:hidden"
                                aria-label="Abrir menú"
                            >
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" className="w-72">
                            <SheetHeader>
                                <SheetTitle>Menú</SheetTitle>
                                <SheetDescription className="sr-only">
                                    Navegación principal de ReUsa
                                </SheetDescription>
                            </SheetHeader>
                            <nav
                                aria-label="Móvil"
                                className="flex flex-col gap-1 px-4"
                            >
                                {navItems.map((item) => (
                                    <Button
                                        key={item.title}
                                        variant="ghost"
                                        className="justify-start"
                                        asChild
                                    >
                                        <Link href={item.href}>
                                            {item.title}
                                        </Link>
                                    </Button>
                                ))}
                                <Button asChild>
                                    <Link href={create()}>
                                        <Plus aria-hidden />
                                        Publicar objeto
                                    </Link>
                                </Button>
                                {user ? (
                                    <Button
                                        variant="ghost"
                                        className="justify-start"
                                        asChild
                                    >
                                        <Link href={myPublications()}>
                                            Mis publicaciones
                                        </Link>
                                    </Button>
                                ) : (
                                    <>
                                        <Button
                                            variant="ghost"
                                            className="justify-start"
                                            asChild
                                        >
                                            <Link href={login()}>
                                                Iniciar sesión
                                            </Link>
                                        </Button>
                                        <Button asChild>
                                            <Link href={register()}>
                                                Crear cuenta
                                            </Link>
                                        </Button>
                                    </>
                                )}
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </header>

            <main id="contenido" className="flex-1">
                {children}
            </main>

            <footer className="border-t bg-paper">
                <div className="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
                    <div className="space-y-3">
                        <Brand />
                        <p className="max-w-xs text-sm text-muted-foreground">
                            Economía circular entre vecinos: dona, intercambia o
                            vende lo que ya no usas.
                        </p>
                    </div>
                    <nav
                        aria-label="Pie de página"
                        className="space-y-2 text-sm"
                    >
                        <p className="type-label text-muted-foreground">
                            Explorar
                        </p>
                        <ul className="space-y-1.5">
                            <li>
                                <Link
                                    href={catalog()}
                                    className="hover:underline"
                                >
                                    Catálogo
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={create()}
                                    className="hover:underline"
                                >
                                    Publicar un objeto
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={terms()}
                                    className="hover:underline"
                                >
                                    Términos y condiciones
                                </Link>
                            </li>
                        </ul>
                    </nav>
                    <div className="space-y-2 text-sm">
                        <p className="type-label text-muted-foreground">
                            Proyecto
                        </p>
                        <p>
                            Proyecto Integrador · Politécnico Colombiano Jaime
                            Isaza Cadavid · ODS 12
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
