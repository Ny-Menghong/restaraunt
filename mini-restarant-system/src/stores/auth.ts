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
                if (response.user?.status !== "active") {
                    throw new Error("Your account is pending admin approval");
                }
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
                this.token = '';
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                return response;
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
        },
        async fetchProfile() {
            const response = await authService.getProfile();
            if (response.user) {
                this.user = response.user;
                localStorage.setItem('user', JSON.stringify(response.user));
            }
            return response;
        },
        async updateProfile(data: FormData) {
            const response = await authService.updateProfile(data);
            if (response.user) {
                this.user = response.user;
                localStorage.setItem('user', JSON.stringify(response.user));
            }
            return response;
        }
    }
})
