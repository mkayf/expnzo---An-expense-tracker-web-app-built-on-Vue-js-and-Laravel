<script setup>
import { computed, handleError, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Form, Field } from 'vee-validate';
import { transactionSchema } from '../utils/validationSchema.js';
import { createCategory, getCategories } from '../services/category.service.js';
import SubmitButton from './ui/SubmitButton.vue';
import { debounce, getCurrentDate } from '../utils/helpers.js';
import { showTransaction, storeTransaction, updateTransaction } from '../services/transaction.service.js';
import { ElMessage } from 'element-plus';
import { ArrowTrendingDownIcon, ArrowTrendingUpIcon, ArrowsRightLeftIcon, BanknotesIcon, CalendarDaysIcon, PencilSquareIcon, TagIcon } from '@heroicons/vue/24/outline';
import { Check, Close } from '@element-plus/icons-vue'
import useCategoryStore from '../stores/categoryStore.js';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    editId: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['close-dialog', 'transaction-saved']);

const saveTransactionLoader = ref(false);
const showTransactionLoader = ref(false);
const transactionAmount = ref(0);
const transactionType = ref('expense');

const initialValues = {
    type: 'expense',
    amount: null,
    transaction_date: getCurrentDate(),
    category_id: null,
    note: null
};

const formRef = ref();

const categoriesLoader = ref(false);
const newCategoryVal = ref('')
const newCategoryInputVisible = ref(false)
const newCategoryInputRef = ref()
const newCategoryInputLoader = ref(false);
const transactionTypeForCategory = ref('expense');
const categoryListRef = ref(null);
const hasScrollableCategories = ref(false);
const categoryScrollThumbHeight = ref(100);
const categoryScrollThumbTop = ref(0);
let categoriesTimer;
const categoryStore = useCategoryStore();

const formatted = (value) => {
    if (value === null || value === undefined || value === '') return ''
    return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const parsed = (value) => {
    if (!value) return ''
    return value.replace(/,/g, '')
}

const dialogVisible = computed({
    get: () => props.visible,
    set: () => {
        closeDialog();
    }
});

const closeDialog = () => {
    emit('close-dialog');
}

const categories = computed(() => {
    return transactionTypeForCategory.value === 'expense' ? categoryStore.expenseCategories : categoryStore.incomeCategories;
})

const updateCategoryScrollIndicator = () => {
    const list = categoryListRef.value;
    if (!list) return;

    const maxScroll = list.scrollHeight - list.clientHeight;
    hasScrollableCategories.value = maxScroll > 1;
    if (maxScroll <= 1) {
        categoryScrollThumbHeight.value = 100;
        categoryScrollThumbTop.value = 0;
        return;
    }

    categoryScrollThumbHeight.value = Math.max(16, (list.clientHeight / list.scrollHeight) * 100);
    categoryScrollThumbTop.value = (list.scrollTop / maxScroll)
        * (100 - categoryScrollThumbHeight.value);
};

const handleCategoryListScroll = () => {
    updateCategoryScrollIndicator();
};

watch(categories, async () => {
    await nextTick();
    updateCategoryScrollIndicator();
}, { deep: true });

onMounted(() => {
    window.addEventListener('resize', updateCategoryScrollIndicator);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateCategoryScrollIndicator);
});


const getCachedCategories = (type) => {
    return type === 'expense' ? categoryStore.expenseCategories : categoryStore.incomeCategories;
}

const fetchCategories = async (type = 'expense') => {
    try {
        categoriesLoader.value = true;

        if (getCachedCategories(type)?.length) {
            return;
        }

        const response = await getCategories(type);
        if (response.data.success) {
            // categories.value = response.data.data;
            categoryStore.setCategories(type, response.data.data);
        }
    }
    catch (e) {
        handleError(e);
    }
    finally {
        categoriesLoader.value = false;
        await nextTick();
        updateCategoryScrollIndicator();
    }
}

// for datepicker component
const shortcuts = [
    {
        text: 'Today',
        value: new Date(),
    },
    {
        text: 'Yesterday',
        value: () => {
            const date = new Date()
            date.setTime(date.getTime() - 3600 * 1000 * 24)
            return date
        },
    },
    {
        text: 'A week ago',
        value: () => {
            const date = new Date()
            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7)
            return date
        },
    },
]

const showNewCategoryInput = () => {
    newCategoryInputVisible.value = true
    nextTick(() => {
        newCategoryInputRef.value.input.focus()
    })
}

const clearSelectedCategory = () => {
    formRef.value?.setFieldValue('category_id', null);
    newCategoryInputVisible.value = false;
    newCategoryVal.value = '';
}

const transactionTypeChanged = (type) => {
    if (type !== 'expense' && type !== 'income') return;
    transactionTypeForCategory.value = type;
    clearSelectedCategory();
    fetchCategories(type);
}

const debouncedTransactionTypeChanged = debounce(transactionTypeChanged, 500);


const clearCategoryVal = () => {
    newCategoryInputVisible.value = false;
    newCategoryVal.value = '';
}

const handleNewCategory = async () => {
    try {
        newCategoryInputLoader.value = true;
        if (!newCategoryVal.value || newCategoryVal.value.trim() === '' || (transactionTypeForCategory.value !== 'expense' && transactionTypeForCategory.value !== 'income')) {
            ElMessage({
                type: 'warning',
                message: 'Please enter a category name and select a valid transaction type'
            });
            return;
        }

        const response = await createCategory({
            name: newCategoryVal.value,
            type: transactionTypeForCategory.value
        });

        if (response.data?.success && response.data?.category) {
            newCategoryInputVisible.value = false;
            newCategoryVal.value = '';
            categoryStore.addCategory(response.data?.category?.type, response.data?.category);
        }

    } catch (e) {
        handleError(e);
    } finally {
        newCategoryInputLoader.value = false;
    }

}

const saveTransaction = async (formData, { resetForm }) => {
    try {
        if (!formData) return;
        saveTransactionLoader.value = true;

        const payload = {
            ...formData,
            ...(props.editId ? { id: props.editId } : {})
        };

        const response = props.editId
            ? await updateTransaction(payload)
            : await storeTransaction(payload);

        if (response.data.success) {
            resetForm({ value: initialValues })
            closeDialog();
            ElMessage({
                type: 'success',
                message: props.editId ? 'Transaction updated successfully' : response.data.message
            });
            emit('transaction-saved');
        }
    }
    catch (e) {
        handleError(e)
    }
    finally {
        saveTransactionLoader.value = false;
    }

}

const handleShowTransaction = async () => {
    try {
        showTransactionLoader.value = true;
        const response = await showTransaction(props.editId);
        if (response.data?.success) {
            const selectedType = response.data?.data?.type ?? 'expense';
            transactionTypeForCategory.value = selectedType;
            await fetchCategories(selectedType);

            formRef.value?.setValues({
                type: selectedType,
                amount: response.data?.data?.amount ?? null,
                transaction_date: response.data?.data?.transaction_date ?? getCurrentDate(),
                category_id: response.data?.data?.category_id ?? null,
                note: response.data?.data?.note ?? null
            })
        }
    } catch (e) {
        handleError(e);
    } finally {
        showTransactionLoader.value = false;
    }
}


watch(() => props.visible, async (val) => {
    if (!val) return;

    if (props.editId) {
        await handleShowTransaction();
        await nextTick();
        updateCategoryScrollIndicator();
        return;
    }

    transactionTypeForCategory.value = 'expense';
    formRef.value?.resetForm({ value: initialValues });
    await fetchCategories('expense');
});

watch(() => props.editId, (val) => {
    if (val) {
        handleShowTransaction();
    } else {
        transactionTypeForCategory.value = 'expense';
        formRef.value?.resetForm({ value: initialValues });
    }
});



</script>

<template>
    <el-dialog v-model="dialogVisible" align-center width="min(700px, calc(100vw - 24px))" class="transaction-dialog">
        <template #header>
            <h2 class="text-md sm:text-lg font-semibold" v-if="!editId">Add Transaction</h2>
            <h2 class="text-md sm:text-lg font-semibold" v-else>Edit Transaction</h2>
            <p class="text-xs" v-if="!editId">Record your income or expenses with the details below.</p>
            <p class="text-xs" v-else>Update the transaction details below.</p>
        </template>
        <div v-loading="showTransactionLoader">
            <Form ref="formRef" @submit="saveTransaction" :validation-schema="transactionSchema"
                :initial-values="initialValues" v-slot="{ resetForm }">
                <el-row :gutter="8">
                    <el-col :span="24">
                        <Field name="type" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-5 sm:!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <ArrowsRightLeftIcon class="h-3.5 w-3.5" />
                                        Transaction type
                                    </span>
                                </template>
                                <el-radio-group :model-value="field.value" @update:model-value="handleChange"
                                    @change="debouncedTransactionTypeChanged(field.value)"
                                    class="!grid w-full grid-cols-2 gap-2 sm:gap-3">
                                    <el-radio value="expense"
                                        class="transaction-type-option !m-0 !mr-0 !flex !h-auto !w-full !items-center !whitespace-normal rounded-lg border !px-2 py-2.5 sm:!px-3"
                                        :class="field.value === 'expense'
                                            ? '!border-(--primary-green) bg-(--el-color-primary-light-9)'
                                            : '!border-gray-200 bg-white hover:!border-gray-300 dark:!border-[var(--el-border-color)] dark:bg-[var(--app-surface)] dark:hover:!border-[var(--el-color-primary)]'">
                                        <span class="flex min-w-0 items-center gap-1.5 sm:gap-2.5">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md sm:h-8 sm:w-8"
                                                :class="field.value === 'expense'
                                                    ? 'bg-(--primary-green) text-white'
                                                    : 'bg-(--bg-light-gray) text-(--secondary-gray)'">
                                                <ArrowTrendingDownIcon class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 leading-tight">
                                                <span
                                                    class="block text-xs font-semibold text-(--text-charcoal) sm:text-sm">Expense</span>
                                                <span
                                                    class="block text-[10px] font-normal text-(--secondary-gray) sm:text-[11px]">Money
                                                    out</span>
                                            </span>
                                        </span>
                                    </el-radio>
                                    <el-radio value="income"
                                        class="transaction-type-option !m-0 !mr-0 !flex !h-auto !w-full !items-center !whitespace-normal rounded-lg border !px-2 py-2.5 sm:!px-3"
                                        :class="field.value === 'income'
                                            ? '!border-(--primary-green) bg-(--el-color-primary-light-9)'
                                            : '!border-gray-200 bg-white hover:!border-gray-300 dark:!border-[var(--el-border-color)] dark:bg-[var(--app-surface)] dark:hover:!border-[var(--el-color-primary)]'">
                                        <span class="flex min-w-0 items-center gap-1.5 sm:gap-2.5">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md sm:h-8 sm:w-8"
                                                :class="field.value === 'income'
                                                    ? 'bg-(--primary-green) text-white'
                                                    : 'bg-(--bg-light-gray) text-(--secondary-gray)'">
                                                <ArrowTrendingUpIcon class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 leading-tight">
                                                <span
                                                    class="block text-xs font-semibold text-(--text-charcoal) sm:text-sm">Income</span>
                                                <span
                                                    class="block text-[10px] font-normal text-(--secondary-gray) sm:text-[11px]">Money
                                                    in</span>
                                            </span>
                                        </span>
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :sm="12">
                        <Field name="amount" v-slot="{ field, handleChange, errorMessage }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-5 sm:!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <BanknotesIcon class="h-3.5 w-3.5" />
                                        Amount
                                    </span>
                                </template>
                                <el-input-number class="!w-full" :model-value="field.value"
                                    @update:model-value="handleChange" :precision="2" :step="1" :formatter="formatted"
                                    :parser="parsed" :max="100000000" :value-on-clear="null" placeholder="0.00" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :sm="12">
                        <Field name="transaction_date" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-5 sm:!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <CalendarDaysIcon class="h-3.5 w-3.5" />
                                        Date
                                    </span>
                                </template>
                                <el-date-picker class="!w-full [&_.el-input__wrapper]:w-full" :model-value="field.value"
                                    @update:model-value="handleChange" type="date" placeholder="Pick a day"
                                    :shortcuts="shortcuts" value-format="YYYY-MM-DD" :editable="false" :clearable="true"
                                    popper-class="transaction-date-picker-popper" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :span="24">
                        <Field name="note" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-5 sm:!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <PencilSquareIcon class="h-3.5 w-3.5" />
                                        Note
                                    </span>
                                </template>
                                <el-input :model-value="field.value" @update:model-value="handleChange"
                                    placeholder="e.g. Lunch with friends" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :span="24">
                        <Field name="category_id" v-slot="{ field, handleChange, errorMessage }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-5 sm:!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <TagIcon class="h-3.5 w-3.5" />
                                        Categories
                                    </span>
                                </template>
                                <el-skeleton animated v-if="categoriesLoader">
                                    <template #template>
                                        <div class="flex h-[88px] flex-wrap content-start gap-2 overflow-hidden">
                                            <el-skeleton-item variant="rect" class="!h-7 !w-20 !rounded-full" />
                                            <el-skeleton-item variant="rect" class="!h-7 !w-24 !rounded-full" />
                                            <el-skeleton-item variant="rect" class="!h-7 !w-16 !rounded-full" />
                                            <el-skeleton-item variant="rect" class="!h-7 !w-28 !rounded-full" />
                                            <el-skeleton-item variant="rect" class="!h-7 !w-20 !rounded-full" />
                                        </div>
                                    </template>
                                </el-skeleton>
                                <div v-else class="category-picker">
                                    <div class="category-scroll-container">
                                        <div ref="categoryListRef" class="category-scroll-area" role="region"
                                            aria-label="Available categories" tabindex="0"
                                            @scroll="handleCategoryListScroll">
                                            <div class="flex flex-wrap content-start items-center gap-2">
                                                <el-check-tag type="primary" :checked="field.value === category.id"
                                                    @change="handleChange(field.value === category.id ? null : category.id)"
                                                    v-for="category in categories" :key="category.id"
                                                    class="!rounded-full !px-3 !py-1 !text-xs">
                                                    {{ category.name }}
                                                </el-check-tag>
                                            </div>
                                        </div>
                                        <div v-if="hasScrollableCategories" class="category-scroll-track"
                                            aria-hidden="true">
                                            <span class="category-scroll-thumb" :style="{
                                                height: `${categoryScrollThumbHeight}%`,
                                                top: `${categoryScrollThumbTop}%`,
                                            }" />
                                        </div>
                                    </div>
                                    <div class="category-picker-actions">
                                        <div v-if="newCategoryInputVisible" class="flex flex-wrap items-center gap-2">
                                            <el-input ref="newCategoryInputRef" v-model="newCategoryVal" class="w-36"
                                                size="small" placeholder="Category name"
                                                @keyup.enter="handleNewCategory" :disabled="newCategoryInputLoader" />
                                            <div class="flex items-center">
                                                <el-button plain type="primary" size="small" :icon="Check" circle
                                                    @click="handleNewCategory" :disabled="newCategoryInputLoader" />
                                                <el-button size="small" plain type="danger" :icon="Close" circle
                                                    @click="clearCategoryVal" :disabled="newCategoryInputLoader" />
                                            </div>
                                        </div>
                                        <el-button v-else size="small" @click="showNewCategoryInput">
                                            + New
                                        </el-button>
                                    </div>
                                </div>
                            </el-form-item>
                        </Field>
                    </el-col>
                </el-row>
                <div class="transaction-form-actions">
                    <el-button native-type="button" @click="closeDialog">Cancel</el-button>
                    <SubmitButton :text="editId ? 'Update' : 'Save'" :is-loading="saveTransactionLoader" />
                </div>
            </Form>
        </div>
    </el-dialog>
</template>

<style scoped>
.category-picker {
    min-width: 0;
}

.category-scroll-container {
    position: relative;
    height: 88px;
}

.category-scroll-area {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    padding-right: 12px;
    overscroll-behavior: contain;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.category-scroll-area::-webkit-scrollbar {
    display: none;
}

.category-scroll-track {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 6px;
    border-radius: 9999px;
    background-color: var(--el-fill-color-light);
    pointer-events: none;
}

.category-scroll-thumb {
    position: absolute;
    right: 0;
    left: 0;
    min-height: 14px;
    border-radius: 9999px;
    background-color: var(--el-color-primary);
}

.category-picker-actions {
    display: flex;
    min-width: 0;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}
</style>

<style>
@media (max-width: 640px) {
    .transaction-date-picker-popper.el-popper {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        inset: 50% auto auto 50% !important;
        transform: translate(-50%, -50%) !important;
        width: min(340px, calc(100vw - 24px)) !important;
        max-height: calc(100dvh - 24px) !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
    }

    .transaction-date-picker-popper .el-popper__arrow {
        display: none !important;
    }

    .transaction-date-picker-popper .el-picker-panel,
    .transaction-date-picker-popper .el-date-picker,
    .transaction-date-picker-popper .el-date-picker.has-sidebar {
        width: 100% !important;
    }

    /* shortcuts: sidebar -> horizontal row on top */
    .transaction-date-picker-popper .el-picker-panel__sidebar {
        position: static !important;
        display: flex;
        gap: 6px;
        width: 100% !important;
        padding: 8px !important;
        box-sizing: border-box;
        overflow-x: auto;
        border-right: 0 !important;
        border-bottom: 1px solid var(--el-border-color-lighter);
    }

    .transaction-date-picker-popper .el-picker-panel__shortcut {
        width: auto !important;
        flex-shrink: 0;
        white-space: nowrap;
        padding: 4px 12px !important;
        border-radius: 9999px;
        border: 1px solid var(--el-border-color-lighter);
        line-height: 20px;
    }

    .transaction-date-picker-popper .el-picker-panel__body {
        margin-left: 0 !important;
        min-width: 0 !important;
    }

    .transaction-date-picker-popper .el-date-picker__header {
        margin: 8px 12px !important;
    }

    .transaction-date-picker-popper .el-picker-panel__content {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 8px 8px;
        box-sizing: border-box;
    }

    .transaction-date-picker-popper .el-date-table {
        width: 100%;
    }

    .transaction-date-picker-popper .el-date-table th {
        white-space: nowrap;
        padding: 4px 0;
    }
}
</style>
