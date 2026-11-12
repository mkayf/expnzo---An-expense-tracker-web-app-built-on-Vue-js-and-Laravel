import api from "./axios";

export const getCategories = (type) => {
    return api.get(`/get-categories?type=${type}`);
}

export const listCategories = (params = {}) => {
    return api.get('/categories', { params });
}

export const exportCategories = (params = {}) => {
    return api.get('/categories/export', {
        params,
        responseType: 'blob',
    });
}

export const exportCategoriesPdf = (params = {}) => {
    return api.get('/categories/export/pdf', {
        params,
        responseType: 'blob',
    });
}

export const createCategory = (data) => {
    return api.post('/create-custom-category', data);
}

export const updateCategory = (id, data) => {
    return api.patch(`/categories/${id}`, data);
}

export const deleteCategory = (id) => {
    return api.delete(`/categories/${id}`);
}

export const bulkDeleteCategories = (ids) => {
    return api.delete('/categories', { data: { ids } });
}