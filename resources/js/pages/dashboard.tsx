import { Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { dashboard } from '@/routes';

export default function Dashboard() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Mi cuenta" />
            <div className="p-4">
                <Heading
                    title={`Hola, ${auth.user?.name ?? ''}`}
                    description="Aquí podrás gestionar tus publicaciones en ReUsa."
                />
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Mi cuenta',
            href: dashboard(),
        },
    ],
};
