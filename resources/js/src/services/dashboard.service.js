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

export const getSpendingInsights = () => {
    return api.get('/dashboard/spending-insights');
}

export const getSpendingCalendar = (month) => {
    return api.get('/dashboard/spending-calendar', {params: {
        month
    }})
}

export const getSpendingCalendarDayTransactions = (date) => {
    return api.get('/dashboard/spending-calendar/day-transactions', { params: { date } });
}