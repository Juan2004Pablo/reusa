export type Option = {
    value: string;
    label: string;
};

export type Category = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    children?: Category[];
};

export type Modality = 'donation' | 'exchange' | 'sale';

export type PublicationStatusValue =
    | 'available'
    | 'reserved'
    | 'delivered'
    | 'sold';

export type PublicationCard = {
    id: number;
    slug: string;
    title: string;
    modality: { value: Modality; label: string };
    status: { value: PublicationStatusValue; label: string };
    price: number | null;
    location: string;
    category: { name: string; slug: string };
    cover_url: string | null;
    created_at: string | null;
};

export type MyPublication = PublicationCard & {
    is_hidden: boolean;
    hidden_reason: string | null;
    status_options: Option[];
};

export type PublicationImage = {
    id: number;
    url: string;
};

export type PublicationDetail = {
    id: number;
    slug: string;
    title: string;
    description: string;
    modality: { value: Modality; label: string };
    condition: { value: string; label: string };
    status: { value: PublicationStatusValue; label: string };
    price: number | null;
    wanted_in_exchange: string | null;
    location: string;
    category: {
        name: string;
        slug: string;
        parent: { name: string; slug: string } | null;
    };
    images: PublicationImage[];
    owner: {
        name: string;
        community: string | null;
        member_since: string | null;
    };
    is_hidden?: boolean;
    created_at: string | null;
    can: { update: boolean; delete: boolean; change_status: boolean };
    status_options: Option[];
};

export type PublicationFormData = {
    slug: string;
    title: string;
    description: string;
    category_id: number;
    parent_category_id: number | null;
    modality: Modality;
    condition: string;
    price: number | null;
    wanted_in_exchange: string | null;
    location: string;
    status: PublicationStatusValue;
    is_closed: boolean;
    images: PublicationImage[];
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
};

export type CatalogFilters = {
    q: string;
    category: string;
    subcategory: string;
    modality: string;
    status: string;
    sort: string;
};
