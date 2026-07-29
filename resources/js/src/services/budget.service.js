import api from './axios';

export const getBudgetData = () => {
    return api.get('/get-budget-data');
}