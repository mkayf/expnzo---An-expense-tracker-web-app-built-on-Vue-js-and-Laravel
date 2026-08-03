import api from './axios';

export const getBudgetData = () => {
    return api.get('/get-budget-data');
}

export const setBudgetData = (data) => {
    return api.post('/set-budget', data);
}