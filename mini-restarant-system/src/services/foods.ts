import api from "../api/axios";

export const foodService = {
    async fetchFood() {
        const response = await api.get('/foods');
        return response.data;
    },
    async createFood(data: { category_id: number; name: string; image?: string; price: number; quantity?: number; description?: string; status?: boolean }) {
        const response = await api.post('/foods', data);
        return response.data;
    },
    async updateFood(id: number, data: { category_id?: number; name?: string; image?: string; price?: number; quantity?: number; description?: string; status?: boolean }) {
        const response = await api.put(`/foods/${id}`, data);
        return response.data;
    },
    async deleteFood(id: number) {
        const response = await api.delete(`/foods/${id}`);
        return response.data;
    },
};
