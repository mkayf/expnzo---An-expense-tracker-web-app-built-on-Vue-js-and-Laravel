import api from "./axios";

export const getUser = () => {
    return api.get('/user');
}

export const deleteAvatar = () => {
    return api.delete('/delete_avatar');    
}

export const saveProfileDetails = (formData) => {
    return api.patch('/save_profile_details', formData);
}

export const saveUserPrefences = (data) => {
    return api.patch('/save-preferences', data);
}
