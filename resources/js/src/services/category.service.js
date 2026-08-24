import api from "./axios";

export const getCategories = (type) => {
    return api.get(`/get-categories?type=${type}`);
}