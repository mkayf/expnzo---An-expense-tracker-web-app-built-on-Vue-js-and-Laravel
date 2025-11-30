import axios from "axios";
import useAuthStore from '../stores/auth';
import { router } from "../router";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true,
    withXSRFToken: true
});

api.interceptors.response.use(
    (res) => res,
    err => {
        const auth = useAuthStore();
        if(err.response?.status === 401){
            auth.logout();
            const currentRoute = router.currentRoute.value;
            if(currentRoute.meta.requiresAuth){
                router.push('/auth/login');
            }
        }

        return Promise.reject(err);
    }
);

export default api;