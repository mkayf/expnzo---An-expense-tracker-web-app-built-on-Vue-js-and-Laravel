import api from "./axios";

export function storeTransaction(data){
    return api.post('/store-transaction', data);
}

export function deleteTransaction(id){
    return api.delete(`/transaction/${id}`);
}