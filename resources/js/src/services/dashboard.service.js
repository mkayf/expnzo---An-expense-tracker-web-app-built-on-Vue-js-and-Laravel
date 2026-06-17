import api from "./axios";

export const getSummaryStats = () => {
    return api.get('/dashboard/stats-summary');
}