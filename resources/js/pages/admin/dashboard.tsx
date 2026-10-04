import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Administración" />
            <div className="p-4">
                <Heading
                    title="Administración"
                    description="Panel de moderación de ReUsa."
                />
            </div>
        </>
    );
}
