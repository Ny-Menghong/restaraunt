import { defineStore } from "pinia";
import type { User } from "../models/user";
import { authService } from "../services/auth";

export const useAuthStor = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null') as User | null,
        token: localStorage.getItem('token') || '',
        loading: false
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(email: string, password: string) {
            this.loading = true;
            try {
                const response = await authService.login({ email, password });
                this.token = response.token;
                this.user = response.user;
                localStorage.setItem('token', response.token);
                localStorage.setItem('user', JSON.stringify(response.user));
            } finally {
                this.loading = false;
            }
        },
        async register(name: string, gender: string, email: string, password: string) {
            this.loading = true;
            try {
                const response = await authService.register({ name, gender, email, password });
                this.token = response.token;
                this.user = response.users;
                localStorage.setItem('token', response.token);
                localStorage.setItem('user', JSON.stringify(response.users));
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                await authService.logout();
            } catch {
                // even if backend fails, clear local state
            } finally {
                this.token = '';
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
            }
        }
    }
})
