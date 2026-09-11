import api from "../api/axios";
export const dashboardService = {
    async fetchDashboard(id:number){
        const response = await api.get('/dashboard_permissions/'+id);
        return response.data
    }
}