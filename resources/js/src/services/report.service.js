import api from "./axios";

const reportParams = (filters) =>
    typeof filters === 'number' ? { months: filters } : filters;

export function getFinancialReport(filters) {
    return api.get('/reports/financial', {
        params: reportParams(filters)
    });
}

export function exportFinancialReportExcel(filters) {
    return api.get('/reports/financial/export', {
        params: reportParams(filters),
        responseType: 'blob',
    });
}

export function exportFinancialReportPdf(filters) {
    return api.get('/reports/financial/export/pdf', {
        params: reportParams(filters),
        responseType: 'blob',
    });
}
