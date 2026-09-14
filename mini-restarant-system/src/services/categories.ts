import api from "../api/axios";

export const categoiyService = {
    async fetchCategories() {
        const response = await api.get('/categories');
        return response.data;
    },
    async createCategory(data: FormData) {
        const response = await api.post('/categories', data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },
    async updateCategory(id: number, data: FormData) {
        data.append('_method', 'PUT');
        const response = await api.post(`/categories/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },
    async deleteCategory(id: number) {
        const response = await api.delete(`/categories/${id}`);
        return response.data;
    },
};