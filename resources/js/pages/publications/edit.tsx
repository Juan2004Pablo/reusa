import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import PublicationForm from '@/components/publications/publication-form';
import { show } from '@/routes/publications';
import type { Category, Option, PublicationFormData } from '@/types';

type Props = {
    publication: PublicationFormData;
    categories: Category[];
    options: { modalities: Option[]; conditions: Option[] };
};

export default function EditPublication({
    publication,
    categories,
    options,
}: Props) {
    return (
        <>
            <Head title="Editar publicación" />
            <div className="mx-auto w-full max-w-5xl p-4 md:p-6">
                <Heading
                    title="Editar publicación"
                    description="Actualiza la información o las fotografías de tu objeto."
                />
                <PublicationForm
                    key={publication.slug}
                    categories={categories}
                    options={options}
                    publication={publication}
                    cancelHref={show.url(publication.slug)}
                />
            </div>
        </>
    );
}

EditPublication.layout = {
    breadcrumbs: [{ title: 'Editar publicación', href: '#' }],
};
