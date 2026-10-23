<script setup>
import DataTable from '../components/DataTable.vue';
import { listTransactions } from '../services/transaction.service.js';

const apiService = listTransactions;

const columns = [
    {
        label: '#',
        prop: 'number',
        sortable: true,
    },
    {
        label: 'Amount',
        prop: 'amount',
        sortable: true,
    },
    {
        label: 'Type',
        prop: 'type',
        sortable: false,
    },
    {
        label: 'Category',
        prop: 'name',
        sortable: true,
    },
    {
        label: 'Date',
        prop: 'transaction_date',
        sortable: true,
    },
];

</script>

<template>
    <h1 class="text-2xl font-semibold mb-4">Transactions</h1>
    <DataTable :exportExcel="true" :exportPDF="true" :columns="columns" :apiService="apiService">
        <!-- <template #role="{row}">
            <el-tag>{{ row.role }}</el-tag>
        </template> -->
    </DataTable>
</template>