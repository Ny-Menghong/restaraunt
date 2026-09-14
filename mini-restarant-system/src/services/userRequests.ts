import api from "../api/axios";

export const userRequestService = {
    async fetchUserRequests() {
        const response = await api.get('/user_requests');
        return response.data;
    },
    async createUserRequest(data: { user_id: number; request_qty: number; status?: string }) {
        const response = await api.post('/user_requests', data);
        return response.data;
    },
    async updateUserRequest(id: number, data: { request_qty?: number; status?: string }) {
        const response = await api.put(`/user_requests/${id}`, data);
        return response.data;
    },
    async deleteUserRequest(id: number) {
        const response = await api.delete(`/user_requests/${id}`);
        return response.data;
    },
};