import { Link, useForm } from '@inertiajs/react';
import { Gift, HandCoins, Repeat, TriangleAlert } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import ImageUploader from '@/components/publications/image-uploader';
import type { ImageItem } from '@/components/publications/image-uploader';
import { Button } from '@/components/ui/button';
import SectionHeading from '@/components/market/section-heading';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { formatInteger } from '@/lib/format';
import { cn } from '@/lib/utils';
import { terms } from '@/routes';
import { index as catalog, store, update } from '@/routes/publications';
import type { Category, Modality, Option, PublicationFormData } from '@/types';

type Props = {
    categories: Category[];
    options: { modalities: Option[]; conditions: Option[] };
    /** Si se envía, el formulario edita esa publicación; si no, crea una nueva. */
    publication?: PublicationFormData;
    cancelHref: string;
};

const modalityMeta: Record<Modality, { icon: LucideIcon; text: string }> = {
    donation: { icon: Gift, text: 'Lo regalas a quien lo necesite.' },
    exchange: { icon: Repeat, text: 'Lo cambias por algo que te sirva.' },
    sale: { icon: HandCoins, text: 'Lo vendes por un precio en pesos.' },
};

const prohibited = [
    'medicamentos',
    'sustancias controladas',
    'armas',
    'artículos de carácter sexual',
    'productos de aseo e higiene personal usados',
    'productos perecederos',
];

export default function PublicationForm({
    categories,
    options,
    publication,
    cancelHref,
}: Props) {
    const isEdit = publication !== undefined;
    const [parentId, setParentId] = useState(
        publication?.parent_category_id
            ? String(publication.parent_category_id)
            : '',
    );
    const [items, setItems] = useState<ImageItem[]>(
        () =>
            publication?.images.map((image) => ({
                key: `existing-${image.id}`,
                kind: 'existing' as const,
                id: image.id,
                url: image.url,
            })) ?? [],
    );

    const form = useForm({
        title: publication?.title ?? '',
        description: publication?.description ?? '',
        category_id: publication ? String(publication.category_id) : '',
        modality: (publication?.modality ?? 'donation') as Modality,
        condition: publication?.condition ?? '',
        price: publication?.price ? String(publication.price) : '',
        wanted_in_exchange: publication?.wanted_in_exchange ?? '',
        location: publication?.location ?? '',
    });

    // Libera las URL de previsualización al salir del formulario.
    const itemsRef = useRef(items);

    useEffect(() => {
        itemsRef.current = items;
    }, [items]);

    useEffect(
        () => () => {
            itemsRef.current.forEach((item) => {
                if (item.kind === 'new') {
                    URL.revokeObjectURL(item.url);
                }
            });
        },
        [],
    );

    const errors = form.errors as Record<string, string | undefined>;
    const imageErrors = [
        ...new Set(
            Object.entries(errors)
                .filter(
                    ([key]) =>
                        key === 'images' ||
                        key.startsWith('images.') ||
                        key.startsWith('image_order'),
                )
                .map(([, message]) => message)
                .filter((message): message is string => Boolean(message)),
        ),
    ];

    const micro =
        categories.find((c) => String(c.id) === parentId)?.children ?? [];
    const modality = form.data.modality;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        const files: File[] = [];
        const order = items.map((item) =>
            item.kind === 'existing'
                ? `existing:${item.id}`
                : `new:${files.push(item.file) - 1}`,
        );

        form.transform((data) => ({
            ...data,
            price: data.modality === 'sale' ? data.price : '',
            wanted_in_exchange:
                data.modality === 'exchange' ? data.wanted_in_exchange : '',
            images: files,
            image_order: order,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        form.post(isEdit ? update.url(publication.slug) : store.url(), {
            forceFormData: true,
            preserveScroll: true,
            onError: () =>
                requestAnimationFrame(() =>
                    document
                        .querySelector<HTMLElement>('[aria-invalid="true"]')
                        ?.focus(),
                ),
        });
    };

    const invalid = (field: string) =>
        errors[field] ? ('true' as const) : undefined;

    return (
        <form
            onSubmit={submit}
            noValidate
            className="space-y-12"
            encType="multipart/form-data"
        >
            <aside
                aria-label="Productos no admitidos"
                className="flex gap-3 rounded-md border-2 border-dashed border-note-ink/40 bg-note px-4 py-3 text-note-ink"
            >
                <TriangleAlert className="mt-0.5 size-5 shrink-0" aria-hidden />
                <p className="text-sm">
                    <strong className="font-semibold">
                        Productos no admitidos:
                    </strong>{' '}
                    {prohibited.join(', ')}. La administración puede retirar las
                    publicaciones que incumplan las reglas.{' '}
                    <Link
                        href={terms()}
                        className="font-semibold underline underline-offset-4"
                    >
                        Ver términos y condiciones
                    </Link>
                </p>
            </aside>

            <section aria-labelledby="sec-1">
                <SectionHeading
                    id="sec-1"
                    index="1"
                    title="Información básica"
                    description="Un buen título y una descripción clara ayudan a que tu objeto encuentre un nuevo hogar."
                />
                <div className="grid gap-5 pt-6">
                    <div className="grid gap-2">
                        <Label htmlFor="title">Título</Label>
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            maxLength={120}
                            placeholder="Ej.: Bicicleta de montaña rodado 26"
                            aria-invalid={invalid('title')}
                            aria-describedby="title-error"
                            autoFocus={!isEdit}
                            required
                        />
                        <InputError id="title-error" message={errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Descripción</Label>
                        <Textarea
                            id="description"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                            maxLength={3000}
                            rows={5}
                            placeholder="Cuenta cómo es, qué incluye, medidas, si tiene detalles…"
                            aria-invalid={invalid('description')}
                            aria-describedby="description-error description-hint"
                            required
                        />
                        <p
                            id="description-hint"
                            className="text-right text-xs text-muted-foreground"
                        >
                            {form.data.description.length}/3000
                        </p>
                        <InputError
                            id="description-error"
                            message={errors.description}
                        />
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="parent-category">Categoría</Label>
                            <Select
                                value={parentId}
                                onValueChange={(value) => {
                                    setParentId(value);
                                    form.setData('category_id', '');
                                }}
                            >
                                <SelectTrigger
                                    id="parent-category"
                                    className="w-full"
                                    aria-invalid={invalid('category_id')}
                                >
                                    <SelectValue placeholder="Elige una categoría" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((category) => (
                                        <SelectItem
                                            key={category.id}
                                            value={String(category.id)}
                                        >
                                            {category.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="category">Subcategoría</Label>
                            <Select
                                value={form.data.category_id}
                                onValueChange={(value) =>
                                    form.setData('category_id', value)
                                }
                                disabled={micro.length === 0}
                            >
                                <SelectTrigger
                                    id="category"
                                    className="w-full"
                                    aria-invalid={invalid('category_id')}
                                    aria-describedby="category-error"
                                >
                                    <SelectValue
                                        placeholder={
                                            parentId
                                                ? 'Elige una subcategoría'
                                                : 'Primero elige la categoría'
                                        }
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    {micro.map((category) => (
                                        <SelectItem
                                            key={category.id}
                                            value={String(category.id)}
                                        >
                                            {category.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <InputError
                            id="category-error"
                            className="sm:col-span-2 sm:-mt-3"
                            message={errors.category_id}
                        />
                    </div>
                </div>
            </section>

            <section aria-labelledby="sec-2">
                <SectionHeading
                    id="sec-2"
                    index="2"
                    title="Modalidad"
                    description="¿Qué quieres hacer con el objeto?"
                />
                <div className="grid gap-5 pt-6">
                    <fieldset
                        className="grid gap-3 sm:grid-cols-3"
                        disabled={publication?.is_closed}
                    >
                        <legend className="sr-only">Modalidad</legend>
                        {options.modalities.map((option) => {
                            const meta = modalityMeta[option.value as Modality];
                            const checked = modality === option.value;

                            return (
                                <label
                                    key={option.value}
                                    className={cn(
                                        'flex cursor-pointer flex-col gap-1 rounded-md border-2 p-4 transition-colors has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-ring/50',
                                        checked
                                            ? 'border-foreground bg-card'
                                            : 'hover:bg-accent/50',
                                        publication?.is_closed &&
                                            'cursor-not-allowed opacity-60',
                                    )}
                                >
                                    <input
                                        type="radio"
                                        name="modality"
                                        value={option.value}
                                        checked={checked}
                                        onChange={() =>
                                            form.setData(
                                                'modality',
                                                option.value as Modality,
                                            )
                                        }
                                        className="sr-only"
                                    />
                                    <span className="flex items-center gap-2 font-medium">
                                        <meta.icon
                                            className="size-5 text-primary"
                                            aria-hidden
                                        />
                                        {option.label}
                                    </span>
                                    <span className="text-sm text-muted-foreground">
                                        {meta.text}
                                    </span>
                                </label>
                            );
                        })}
                    </fieldset>
                    {publication?.is_closed && (
                        <p className="text-sm text-muted-foreground">
                            La modalidad no se puede cambiar porque la
                            publicación ya está cerrada.
                        </p>
                    )}
                    <InputError message={errors.modality} />

                    {modality === 'sale' && (
                        <div className="grid gap-2 sm:max-w-xs">
                            <Label htmlFor="price">Precio (COP)</Label>
                            <div className="relative">
                                <span
                                    className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground"
                                    aria-hidden
                                >
                                    $
                                </span>
                                <Input
                                    id="price"
                                    inputMode="numeric"
                                    autoComplete="off"
                                    value={formatInteger(form.data.price)}
                                    onChange={(e) =>
                                        form.setData(
                                            'price',
                                            e.target.value
                                                .replace(/\D/g, '')
                                                .slice(0, 10),
                                        )
                                    }
                                    placeholder="50.000"
                                    className="pl-7"
                                    aria-invalid={invalid('price')}
                                    aria-describedby="price-error price-hint"
                                    required
                                />
                            </div>
                            <p
                                id="price-hint"
                                className="text-xs text-muted-foreground"
                            >
                                Solo se muestra el precio: el pago se coordina
                                directamente entre las partes.
                            </p>
                            <InputError
                                id="price-error"
                                message={errors.price}
                            />
                        </div>
                    )}

                    {modality === 'exchange' && (
                        <div className="grid gap-2">
                            <Label htmlFor="wanted">
                                ¿Qué buscas a cambio?{' '}
                                <span className="font-normal text-muted-foreground">
                                    (opcional)
                                </span>
                            </Label>
                            <Input
                                id="wanted"
                                value={form.data.wanted_in_exchange}
                                onChange={(e) =>
                                    form.setData(
                                        'wanted_in_exchange',
                                        e.target.value,
                                    )
                                }
                                maxLength={255}
                                placeholder="Ej.: libros de cocina, una silla…"
                                aria-invalid={invalid('wanted_in_exchange')}
                                aria-describedby="wanted-error"
                            />
                            <InputError
                                id="wanted-error"
                                message={errors.wanted_in_exchange}
                            />
                        </div>
                    )}
                </div>
            </section>

            <section aria-labelledby="sec-3">
                <SectionHeading
                    id="sec-3"
                    index="3"
                    title="Estado y ubicación"
                />
                <div className="grid gap-5 pt-6 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="condition">Estado del objeto</Label>
                        <Select
                            value={form.data.condition}
                            onValueChange={(value) =>
                                form.setData('condition', value)
                            }
                        >
                            <SelectTrigger
                                id="condition"
                                className="w-full"
                                aria-invalid={invalid('condition')}
                                aria-describedby="condition-error"
                            >
                                <SelectValue placeholder="¿Cómo está?" />
                            </SelectTrigger>
                            <SelectContent>
                                {options.conditions.map((option) => (
                                    <SelectItem
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="condition-error"
                            message={errors.condition}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="location">
                            Ubicación o punto de entrega aproximado
                        </Label>
                        <Input
                            id="location"
                            value={form.data.location}
                            onChange={(e) =>
                                form.setData('location', e.target.value)
                            }
                            maxLength={150}
                            placeholder="Ej.: Laureles, cerca al estadio"
                            aria-invalid={invalid('location')}
                            aria-describedby="location-error"
                            required
                        />
                        <InputError
                            id="location-error"
                            message={errors.location}
                        />
                    </div>
                </div>
            </section>

            <section aria-labelledby="sec-4">
                <SectionHeading
                    id="sec-4"
                    index="4"
                    title="Fotografías"
                    description="Agrega de 1 a 4 fotos. La primera será la portada; puedes cambiar el orden con las flechas."
                />
                <div className="pt-6">
                    <ImageUploader
                        items={items}
                        onChange={setItems}
                        errors={imageErrors}
                    />
                </div>
            </section>

            <div className="sticky bottom-0 -mx-4 flex flex-col-reverse gap-2 border-t bg-background/95 px-4 py-4 backdrop-blur sm:static sm:mx-0 sm:flex-row sm:justify-end sm:border-0 sm:bg-transparent sm:p-0">
                <Button variant="outline" asChild>
                    <Link href={cancelHref || catalog()}>Cancelar</Link>
                </Button>
                <Button
                    type="submit"
                    disabled={form.processing}
                    data-test="publication-submit"
                >
                    {form.processing && <Spinner />}
                    {isEdit ? 'Guardar cambios' : 'Publicar objeto'}
                </Button>
            </div>
        </form>
    );
}
