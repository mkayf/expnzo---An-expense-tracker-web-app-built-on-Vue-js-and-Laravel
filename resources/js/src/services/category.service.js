import api from "./axios";

export const getCategories = (type) => {
    return api.get(`/get-categories?type=${type}`);
}

export const createCategory = (data) => {
    return api.post('/create-custom-category', data);
}