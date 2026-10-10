<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import Card from '../../../components/ui/Card.vue';
import { getRecentTransactions } from '../../../services/dashboard.service.js';
import handleError from '../../../utils/handleError.js';
import { formatAmount, formatDate } from '../../../utils/helpers.js';
import useAuthStore from '../../../stores/auth.js';

const props = defineProps({
    update: {
        type: Number,
    }
});

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
    if (!transactions.value || !transactions.value.length) return [];

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

const tagStyle = (type) => ({
    '--el-tag-border-color':
        type === 'income'
            ? 'var(--el-color-primary-light-5)'
            : 'var(--el-color-warning-light-5)',
});

watch(() => props.update, (val) => {
    if (val) {
        fetchRecentTransactions();
    }
});


onMounted(() => {
    fetchRecentTransactions();
})

</script>

<template>
    <Card class="min-w-0" :body_x_padding="false">
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
                <template #default>
                    <div class="transactions-container w-full min-w-0">
                        <div class="transactions-table w-full min-w-0">
                            <el-table :data="transactionsTableData" class="app-table" style="width: 100%"
                                table-layout="fixed" :show-header="true">
                                <el-table-column prop="number" label="#" width="60" align="center" />

                                <el-table-column prop="amount" label="Amount" min-width="130">
                                    <template #default="scope">
                                        <span class="whitespace-nowrap font-semibold">
                                            {{ userCurrency + " " + formatAmount(scope.row.amount, userCurrencyIso) }}
                                        </span>
                                    </template>
                                </el-table-column>

                                <el-table-column prop="type" label="Type" min-width="90">
                                    <template #default="scope">
                                        <el-tag effect="light" round
                                            :type="scope.row.type === 'expense' ? 'warning' : 'primary'"
                                            :style="tagStyle(scope.row.type)">
                                            {{ scope.row.type }}
                                        </el-tag>
                                    </template>
                                </el-table-column>

                                <el-table-column prop="category_name" label="Category" min-width="140"
                                    show-overflow-tooltip />

                                <el-table-column prop="transaction_date" label="Date" min-width="110"
                                    show-overflow-tooltip />

                            </el-table>
                        </div>

                        <div class="transactions-list divide-y divide-gray-100 px-3">
                            <div v-for="row in transactionsTableData" :key="row.id"
                                class="flex min-w-0 items-start justify-between gap-2 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ row.category_name }}</p>
                                    <p class="text-xs text-gray-500">{{ row.transaction_date }}</p>
                                </div>

                                <div class="flex max-w-[55%] shrink-0 flex-col items-end gap-1">
                                    <span class="max-w-full text-right text-sm font-semibold [overflow-wrap:anywhere]">
                                        {{ userCurrency + " " + formatAmount(row.amount, userCurrencyIso) }}
                                    </span>
                                    <el-tag size="small" effect="light" round
                                        :type="row.type === 'expense' ? 'warning' : 'primary'"
                                        :style="tagStyle(row.type)">
                                        {{ row.type }}
                                    </el-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </el-skeleton>
        </template>
    </Card>
</template>

<style scoped>
.transactions-container {
    container-type: inline-size;
}

.transactions-table {
    display: none;
}

@container (min-width: 600px) {
    .transactions-table {
        display: block;
    }

    .transactions-list {
        display: none;
    }
}

@media (max-width: 1024px) {
    .app-table .el-table__cell {
        padding: 8px 0;
    }

    .app-table .cell {
        padding: 0 8px;
        font-size: 13px;
    }
}
</style>