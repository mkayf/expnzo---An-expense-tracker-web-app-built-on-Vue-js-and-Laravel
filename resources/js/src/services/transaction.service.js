import api from "./axios";

export function storeTransaction(data){
    return api.post('/store-transaction', data);
}

export function updateTransaction(data){
    return api.patch('/update-transaction', data);
}

export function deleteTransaction(id){
    return api.delete(`/transaction/${id}`);
}

export function showTransaction(id){
    return api.get(`/transaction/${id}`)
}

export function listTransactions(params = {}){
    return api.get('/transactions', {
        params: {
            ...params,
        }
    });
}