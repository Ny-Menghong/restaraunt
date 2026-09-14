import api from "../api/axios";

export const authService = {
    async login(credentials: { email: string; password: string }) {
        const { data } = await api.post('/login', credentials);
        return data;
    },
    async register(userData: { name: string; gender: string; email: string; password: string }) {
        const { data } = await api.post('/register', userData);
        return data;
    },
    async logout() {
        const { data } = await api.post('/logout');
        return data;
    },
    async getProfile() {
        const { data } = await api.get('/profile');
        return data;
    },
    async updateProfile(data: FormData) {
        data.append('_method', 'PUT');
        const response = await api.post('/profile', data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },
}
