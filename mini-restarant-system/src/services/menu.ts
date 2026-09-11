import api from "../api/axios";
export const menuService = {
  async fetchMenu(token:string) {
    const response = await api.get('/qr/'+token)
    return response.data
  },
}