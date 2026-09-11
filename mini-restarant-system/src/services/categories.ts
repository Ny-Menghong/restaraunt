import api from "../api/axios";

export const categoiyService = {
    async fetchCategories() {
        const response = await api.get('/categories');
        return response.data;
    },
    async createCategory(data: { name: string; slug?: string; image?: string; description?: string; status?: boolean }) {
        const response = await api.post('/categories', data);
        return response.data;
    },
    async updateCategory(id: number, data: { name?: string; slug?: string; image?: string; description?: string; status?: boolean }) {
        const response = await api.put(`/categories/${id}`, data);
        return response.data;
    },
    async deleteCategory(id: number) {
        const response = await api.delete(`/categories/${id}`);
        return response.data;
    },
};
