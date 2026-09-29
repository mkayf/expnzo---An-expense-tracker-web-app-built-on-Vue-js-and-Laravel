import api from "./axios";

export const getSummaryStats = () => {
    return api.get('/dashboard/stats-summary');
}

export const getIncomeExpense = (months) => {
    return api.get(`/dashboard/income-expense?months=${months}`);
}

export const getExpenseByCategories = (months) => {
    return api.get(`/dashboard/expense-by-categories?months=${months}`);
}

export const getRecentTransactions = () => {
    return api.get('/dashboard/recent-transactions');
}