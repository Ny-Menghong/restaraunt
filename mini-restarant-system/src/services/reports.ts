import api from "../api/axios";

export const reportService = {
    async fetchReports(from?: string, to?: string) {
        const params: any = {};
        if (from) params.from = from;
        if (to) params.to = to;
        const response = await api.get('/reports', { params });
        return response.data;
    },
};
