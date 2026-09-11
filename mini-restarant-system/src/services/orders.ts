import api from "../api/axios";

export const ordersService = {
    async orders(filters?: { status?: string; order_type?: string }) {
        const params: any = {};
        if (filters?.status) params.status = filters.status;
        if (filters?.order_type) params.order_type = filters.order_type;
        const response = await api.get('/orders', { params });
        return response.data;
    },
    async updateStatus(orderId: number, data: { status: string }) {
        const response = await api.put(`/orders/${orderId}`, data);
        return response.data;
    },
    async createOrder(orderData: {
        table_id: number | null;
        customer_id?: number | null;
        order_type: string;
        items: { food_id: number; quantity: number }[];
        payment_method?: string;
        discount?: number;
    }) {
        const response = await api.post('/orders', orderData);
        return response.data;
    },
    async deleteOrder(orderId: number) {
        const response = await api.delete(`/orders/${orderId}`);
        return response.data;
    },
};
