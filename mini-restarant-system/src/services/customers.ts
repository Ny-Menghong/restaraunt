import api from "../api/axios";

export const customerService = {
    async fetchCustomers() {
        const response = await api.get('/customers');
        return response.data;
    },
    async createCustomer(data: { name: string; phone?: string; email?: string; address?: string; note?: string }) {
        const response = await api.post('/customers', data);
        return response.data;
    },
    async updateCustomer(id: number, data: { name?: string; phone?: string; email?: string; address?: string; note?: string }) {
        const response = await api.put(`/customers/${id}`, data);
        return response.data;
    },
    async deleteCustomer(id: number) {
        const response = await api.delete(`/customers/${id}`);
        return response.data;
    },
};
