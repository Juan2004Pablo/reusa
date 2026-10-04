export type User = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    community: string | null;
    role: 'user' | 'admin';
    is_admin: boolean;
    avatar?: string;
};

export type Auth = {
    user: User | null;
};
