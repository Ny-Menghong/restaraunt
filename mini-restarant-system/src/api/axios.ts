import axios from 'axios'
// import { useErrorStore } from '../stores/error';
const baseURL:string = "http://127.0.0.1:8000";
// const baseURL = "https://formal-cumulative-relatives-refurbished.trycloudflare.com";
const api = axios.create({
    baseURL: `${baseURL}/api`,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
    }
})
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})
// const errorStore = useErrorStore();
api.interceptors.response.use(
    (response) => response,
    (error) => {
        // if(error.response?.status === 403){
        //     errorStore.setError({status:403,message:"Forbidden"});
        // }
        // if(error.response?.status === 404){
        //     errorStore.setError({status:404,message:"Forbidden"});
        // }
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
);
export default api
