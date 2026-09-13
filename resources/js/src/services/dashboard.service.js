import api from "./axios";

export const getSummaryStats = () => {
    return api.get('/dashboard/stats-summary');
}

export const getIncomeExpense = (months) => {
    return api.get(`/dashboard/income-expense?months=${months}`);
}