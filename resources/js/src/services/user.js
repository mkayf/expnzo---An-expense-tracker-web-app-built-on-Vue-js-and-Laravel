import api from "./axios";

export const getUser = () => {
    return api.get('/user');
}

export const deleteAvatar = () => {
    return api.delete('/delete-avatar');    
}