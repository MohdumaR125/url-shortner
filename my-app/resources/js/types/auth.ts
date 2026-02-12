export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    company_id?: number | null;
    role?: string;
    company?: Company;
    [key: string]: unknown;
};

export type Company = {
    id: number;
    name: string;
    users_count?: number;
    short_urls_count?: number;
    created_at: string;
    updated_at: string;
};

export type ShortUrl = {
    id: number;
    company_id: number;
    created_by: number;
    original_url: string;
    short_code: string;
    created_at: string;
    updated_at: string;
    creator?: User;
    company?: Company;
};

export type TeamMember = {
    id: number;
    name: string;
    email: string;
    role: string;
    urls_count: number;
    created_at: string;
};

export type Invitation = {
    id: number;
    company_id: number;
    email: string;
    role: string;
    token: string;
    accepted_at: string | null;
};

export type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
