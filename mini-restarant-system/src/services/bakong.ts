import api from "../api/axios";

export const bakongService = {
    async checkout(amount: number) {
        const response = await api.post('/bakong/checkout', { amount });
        return response.data;
    },
    async verify(md5: string) {
        const response = await api.post('/bakong/verify', { md5 });
        return response.data;
    },
};