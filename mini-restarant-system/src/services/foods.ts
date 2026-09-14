import api from "../api/axios";

export const foodService = {
    async fetchFood() {
        const response = await api.get('/foods');
        return response.data;
    },
    async createFood(data: FormData) {
        const response = await api.post('/foods', data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },
    async updateFood(id: number, data: FormData) {
        data.append('_method', 'PUT');
        const response = await api.post(`/foods/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },
    async deleteFood(id: number) {
        const response = await api.delete(`/foods/${id}`);
        return response.data;
    },
};