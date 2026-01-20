import api from "./axios";

export const getUser = () => {
    return api.get('/user');
}

export const deleteAvatar = () => {
    return api.delete('/delete_avatar');    
}

export const saveProfileDetails = (formData) => {
    return api.post('/save_profile_details', formData);
}