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
}
