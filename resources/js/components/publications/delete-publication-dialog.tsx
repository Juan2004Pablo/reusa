import { router } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { destroy } from '@/routes/publications';

/**
 * Diálogo de confirmación para eliminar una publicación.
 * Se controla desde fuera (`open`) para poder abrirlo desde menús desplegables.
 */
export default function DeletePublicationDialog({
    slug,
    title,
    open,
    onOpenChange,
    children,
}: {
    slug: string;
    title: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    children?: ReactNode;
}) {
    const [processing, setProcessing] = useState(false);

    const confirm = () => {
        router.delete(destroy.url(slug), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            {children}
            <DialogContent role="alertdialog">
                <DialogHeader>
                    <DialogTitle>¿Eliminar esta publicación?</DialogTitle>
                    <DialogDescription>
                        «{title}» dejará de aparecer en el catálogo y ya no
                        podrás editarla. Esta acción no se puede deshacer desde
                        la plataforma.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="gap-2 sm:gap-0">
                    <DialogClose asChild>
                        <Button variant="secondary" disabled={processing}>
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        onClick={confirm}
                        disabled={processing}
                        data-test="confirm-delete-publication"
                    >
                        {processing && <Spinner />}
                        Sí, eliminar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
