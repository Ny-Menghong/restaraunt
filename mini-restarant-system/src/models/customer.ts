export interface Customer {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    note: string | null;
    created_at: Date;
    updated_at: Date;
}
