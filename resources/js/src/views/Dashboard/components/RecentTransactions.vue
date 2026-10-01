<script setup>
import { ref, onMounted, computed } from 'vue';
import Card from '../../../components/ui/Card.vue';
import { getRecentTransactions } from '../../../services/dashboard.service.js';
import handleError from '../../../utils/handleError.js';
import { formatAmount, formatDate } from '../../../utils/helpers.js';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import useAuthStore from '../../../stores/auth.js';
import { deleteTransaction } from '../../../services/transaction.service.js';

const loading = ref(false);
const transactions = ref([]);
const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency;
const userCurrencyIso = authStore.user?.preferences?.currency_iso;

const fetchRecentTransactions = async () => {
    try {
        loading.value = true;
        const response = await getRecentTransactions();
        if (response.data?.success) {
            transactions.value = response.data?.data;
            console.log('transactions: ', transactions.value);
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
}

const transactionsTableData = computed(() => {
    if (!transactions.value || !transactions.value.length) return;

    return transactions.value.map((item, i) => {
        return {
            id: item.id,
            number: i + 1,
            amount: item.amount ?? 0,
            type: item.type ?? '-',
            category_name: item.category?.name.length > 30 ? item.category?.name.substr(0, 30) + '...' : item.category?.name ?? '-',
            transaction_date: formatDate(item.transaction_date) || '-'
        }
    })
});

const handleDeleteTransaction = async (id) => {
    try {
        const confirmed = await ElMessageBox.confirm('Do you want to delete this transaction?', 'Confirm', {
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            type: 'warning'
        }).catch(() => false);

        if(!confirmed) return;

        const response = await deleteTransaction(id);

        if(response.data?.success){
            transactions.value = transactions.value.filter(item => item.id !== id);
        }

    } catch (e) {
        handleError(e)
    }

}

onMounted(() => {
    fetchRecentTransactions();
})

</script>

<template>
    <Card :body_x_padding="false">
        <template #header>
            <el-skeleton :loading="loading" animated :throttle="300">
                <template #template>
                    <el-skeleton-item variant="text" style="width: 140px" />
                </template>
                <template #default>
                    <span>Recent Transactions</span>
                </template>
            </el-skeleton>
        </template>

        <template #addons>
            <el-skeleton :loading="loading" animated :throttle="300">
                <template #template>
                    <el-skeleton-item variant="button" style="width: 80px; height: 24px" />
                </template>
                <template #default>
                    <el-button size="small" type="primary" plain>View more</el-button>
                </template>
            </el-skeleton>
        </template>

        <template #body>
            <el-skeleton :loading="loading" :rows="5" animated :throttle="300" class="p-4">
                <el-table :data="transactionsTableData" style="width: 100%" class="app-table">
                    <el-table-column prop="number" label="#" width="60" />
                    <el-table-column prop="amount" label="Amount" width="180">
                        <template #default="scope">
                            {{ userCurrency + " " + formatAmount(scope.row.amount, userCurrencyIso) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="type" label="Type" width="120">
                        <template #default="scope">
                            <el-tag effect="light" round :type="scope.row.type === 'expense' ? 'warning' : 'primary'"
                                :style="{ '--el-tag-border-color': scope.row.type === 'income' ? 'var(--el-color-primary-light-5)' : 'var(--el-color-warning-light-5)' }">
                                {{ scope.row.type }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="category_name" label="Category" width="180" />
                    <el-table-column prop="transaction_date" label="Date" width="140" />
                    <el-table-column label="Action" width="100">
                        <template #default="scope">
                            <el-button plain type="info" :icon="PencilIcon" size="small" circle />
                            <el-button plain type="danger" :icon="TrashIcon" size="small" circle
                                @click="handleDeleteTransaction(scope.row.id)" />
                        </template>
                    </el-table-column>
                </el-table>
            </el-skeleton>
        </template>
    </Card>
</template>