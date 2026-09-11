import api from "../api/axios";

export const tableService = {
    async fetchTables() {
        const response = await api.get('/tables');
        return response.data;
    },
    async fetchTableMenu(token: string) {
        const response = await api.get('/qr/' + token);
        return response.data;
    },
    async createTable(data: { table_number: string; capacity: number; location: string }) {
        const response = await api.post('/tables', data);
        return response.data;
    },
    async updateTable(id: number, data: { table_number?: string; capacity?: number; location?: string }) {
        const response = await api.put(`/tables/${id}`, data);
        return response.data;
    },
    async deleteTable(id: number) {
        const response = await api.delete(`/tables/${id}`);
        return response.data;
    },
};
