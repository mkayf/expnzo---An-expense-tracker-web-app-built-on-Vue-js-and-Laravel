import api from './axios';

const getCSRFCookie =  async () => {
    return api.get('/sanctum/csrf-cookie');
}

export const login = async (credentials) => {       
    await getCSRFCookie();
    return api.post('/login', credentials);      
}

export const register = async (credentials) => {
    await getCSRFCookie();
    return api.post('/register', credentials);
}                                                            

export const logout = () => {
    return api.post('/logout');
}

export const getUser = () => {
    return api.get('/user');
}

export const userToBeVerified = ($data) => {
    return api.post('/user_to_be_verified', $data);
}