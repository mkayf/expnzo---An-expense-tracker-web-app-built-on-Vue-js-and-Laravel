<script setup>
import DataTable from '../components/DataTable.vue';
import { bulkDeleteTransactions, deleteTransaction, exportTransactions, exportTransactionsPdf, listTransactions } from '../services/transaction.service.js';
import { tagStyle } from '../utils/helpers.js';
import { formatAmount } from '../utils/helpers.js';
import useAuthStore from '../stores/auth.js';
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/outline"
import { ElMessage, ElMessageBox } from 'element-plus';
import 'element-plus/es/components/message/style/css';
import 'element-plus/es/components/message-box/style/css';
import handleError from '../utils/handleError.js';
const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency;
const userCurrencyIso = authStore.user?.preferences?.currency_iso;
import TransactionForm from '../components/TransactionForm.vue';
import { computed, ref } from 'vue';

const apiService = listTransactions;
const excelExportService = exportTransactions;
const pdfExportService = exportTransactionsPdf;
const exportColumns = [
    { label: 'Transaction ID', prop: 'id' },
    { label: 'Amount', prop: 'amount' },
    { label: 'Type', prop: 'type' },
    { label: 'Category', prop: 'category.name' },
    { label: 'Note', prop: 'note' },
    { label: 'Transaction Date', prop: 'transaction_date' },
    { label: 'Created At', prop: 'created_at' },
    { label: 'Updated At', prop: 'updated_at' },
];
const pdfExportColumns = exportColumns.filter((column) => column.prop !== 'note');
const isTransactionModalOpen = ref(false);
const transactionEditId = ref(null);
const refresh = ref(0);
const isBulkDeleting = ref(false);
const bulkActions = computed(() => [
    {
        key: 'delete',
        label: 'Delete',
        showSelectionCount: true,
        type: 'danger',
        icon: TrashIcon,
        loading: isBulkDeleting.value,
    },
]);

const columns = [
    {
        label: 'Amount',
        mobile: 'value',
        prop: 'amount',
        sortable: true,
    },
    {
        label: 'Type',
        mobile: 'badge',
        prop: 'type',
        sortable: false,
    },
    {
        label: 'Category',
        mobile: 'title',
        prop: 'category.name',
        sortable: true,
    },
    {
        label: 'Date',
        mobile: 'subtitle',
        prop: 'transaction_date',
        sortable: true,
    },
    {
        label: 'Actions',
        mobile: 'actions',
        prop: 'actions',
        sortable: false,
    }
];

const openTransactionForm = (id = null) => {
    transactionEditId.value = id;
    isTransactionModalOpen.value = true;
}

const handleDeleteTransaction = async (id) => {
    try {
        await ElMessageBox.confirm(
            'Do you want to delete this transaction?',
            'Confirm',
            {
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                type: 'warning',
            },
        );

        const response = await deleteTransaction(id);
        if (response.data?.success) {
            ElMessage({
                type: 'success',
                message: response.data.message || 'Transaction deleted successfully',
            });
            refresh.value++;
            return;
        }

        throw new Error(response.data?.message || 'Failed to delete transaction');
    } catch (e) {
        if (e === 'cancel' || e === 'close') return;
        handleError(e);
    }
}

const handleBulkAction = async ({ key, ids }) => {
    if (key !== 'delete' || ids.length === 0 || isBulkDeleting.value) return;

    try {
        await ElMessageBox.confirm(
            `Delete ${ids.length} selected transaction${ids.length === 1 ? '' : 's'}? This action cannot be undone.`,
            'Delete selected transactions',
            {
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                type: 'warning',
            },
        );

        isBulkDeleting.value = true;
        const response = await bulkDeleteTransactions(ids);
        if (!response.data?.success) {
            throw new Error(response.data?.message || 'Failed to delete selected transactions');
        }

        ElMessage({
            type: 'success',
            message: response.data.message || `${response.data.deleted_count} transactions deleted successfully`,
        });
        refresh.value++;
    } catch (error) {
        if (error === 'cancel' || error === 'close') return;
        handleError(error);
    } finally {
        isBulkDeleting.value = false;
    }
}

</script>

<template>
    <h1 class="text-2xl font-semibold mb-4">Transactions</h1>
    <DataTable :columns="columns" :apiService="apiService" :default-sort="{ prop: 'transaction_date', order: 'descending' }" :exportExcel="true" :exportService="excelExportService" :exportColumns="exportColumns" :exportPDF="true" :pdfExportService="pdfExportService" :pdfExportColumns="pdfExportColumns" :bulkActions="bulkActions" :update="refresh" @bulk-action="handleBulkAction">
        <template #addons>
            <el-button size="small" type="primary" @click="openTransactionForm()">
                Add transaction
            </el-button>
        </template>
        <template #type="{ row }">
            <el-tag effect="light" round :type="row.type === 'expense' ? 'warning' : 'primary'"
                :style="tagStyle(row.type)">
                {{ row.type }}
            </el-tag>
        </template>
        <template #amount="{ row }">
            {{ userCurrency + " " + formatAmount(row.amount, userCurrencyIso) }}
        </template>
        <template #actions="{row}">
            <el-button :icon="PencilSquareIcon" size="small" type="primary" circle plain @click="openTransactionForm(row.id)" />
            <el-button :icon="TrashIcon"  size="small" type="danger" circle plain @click="handleDeleteTransaction(row.id)" />
        </template>
    </DataTable>

    <TransactionForm v-model:visible="isTransactionModalOpen" @close-dialog="isTransactionModalOpen = false"
            :edit-id="transactionEditId" @transaction-saved="refresh++" />
</template>