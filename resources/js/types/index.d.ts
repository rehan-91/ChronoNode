
export interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    office_id: number | null;
    office?: Office;
}

export interface Office {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
    is_active: boolean;
}

export interface Attendance {
    id: number;
    user_id: number;
    date: string;
    check_in: string | null;
    check_out: string | null;
    status: string;
    working_minutes: number;
    late_minutes: number;
    early_departure_minutes: number;
    overtime_minutes: number;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
};
