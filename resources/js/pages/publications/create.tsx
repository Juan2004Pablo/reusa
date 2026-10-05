import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import PublicationForm from '@/components/publications/publication-form';
import { index as myPublications } from '@/routes/my-publications';
import { create } from '@/routes/publications';
import type { Category, Option } from '@/types';

type Props = {
    categories: Category[];
    options: { modalities: Option[]; conditions: Option[] };
};

export default function CreatePublication({ categories, options }: Props) {
    return (
        <>
            <Head title="Publicar un objeto" />
            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Publicar un objeto"
                    description="Cuéntanos qué quieres compartir con tu comunidad."
                />
                <PublicationForm
                    categories={categories}
                    options={options}
                    cancelHref={myPublications.url()}
                />
            </div>
        </>
    );
}

CreatePublication.layout = {
    breadcrumbs: [{ title: 'Publicar objeto', href: create() }],
};
