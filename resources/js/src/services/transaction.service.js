import api from "./axios";

export function storeTransaction(data){
    return api.post('/store-transaction', data);
}