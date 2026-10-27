<script setup>
import DataTable from '../components/DataTable.vue';
import { listTransactions } from '../services/transaction.service.js';
import { tagStyle } from '../utils/helpers.js';

const apiService = listTransactions;

const columns = [
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
        prop: 'category.name',
        sortable: true,
    },
    {
        label: 'Date',
        prop: 'transaction_date',
        sortable: true,
    },
    {
        label: 'Actions',
        prop: 'actions',
        sortable: false,
    }
];

</script>

<template>
    <h1 class="text-2xl font-semibold mb-4">Transactions</h1>
    <DataTable :columns="columns" :apiService="apiService" :exportExcel="true" :exportPDF="true">
        <template #type="{ row }">
            <el-tag effect="light" round :type="row.type === 'expense' ? 'warning' : 'primary'"
                :style="tagStyle(row.type)">
                {{ row.type }}
            </el-tag>
        </template>
    </DataTable>
</template>