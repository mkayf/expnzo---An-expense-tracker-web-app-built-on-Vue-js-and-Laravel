<script setup>
import DataTable from '../components/DataTable.vue';

const columns = [
    {
        label: 'Name',
        prop: 'name',
        sortable: true,
    },
    {
        label: 'Role',
        prop: 'role',
        sortable: true,
    },
    {
        label: 'Age',
        prop: 'age',
        sortable: true,
    },
    {
        label: 'Company',
        prop: 'company',
        sortable: true,
    },
    {
        label: 'City',
        prop: 'city',
        sortable: true,
    },
];

</script>

<template>
    <h1 class="text-2xl font-semibold mb-4">Transactions</h1>
    <DataTable :exportExcel="true" :exportPDF="true" :columns="columns">
        <template #role="{row}">
            <el-tag>{{ row.role }}</el-tag>
        </template>
    </DataTable>
</template>