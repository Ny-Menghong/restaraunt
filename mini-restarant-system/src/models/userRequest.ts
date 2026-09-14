export interface UserRequest {
    id: number;
    user_id: number;
    request_qty: number;
    status: 'pending' | 'comfirm' | 'cancle';
    user?: {
        id: number;
        name: string;
        email: string;
        gender: string;
        phone: string | null;
        role?: string;
        status: string;
    };
    created_at: Date;
    updated_at: Date;
}