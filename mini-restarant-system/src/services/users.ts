import api from "../api/axios";

export const userService = {
    async fetchUsers() {
        const response = await api.get('/users');
        return response.data;
    },
    async createUser(data: { name: string; gender: string; email: string; password: string; role: string }) {
        const response = await api.post('/users', data);
        return response.data;
    },
    async updateUser(id: number, data: { name?: string; gender?: string; email?: string; password?: string; role?: string }) {
        const response = await api.put(`/users/${id}`, data);
        return response.data;
    },
    async deleteUser(id: number) {
        const response = await api.delete(`/users/${id}`);
        return response.data;
    },
};
