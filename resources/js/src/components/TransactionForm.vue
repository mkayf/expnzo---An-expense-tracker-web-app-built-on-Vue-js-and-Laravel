<script setup>
import { computed, handleError, nextTick, ref, watch } from 'vue';
import { Form, Field } from 'vee-validate';
import { transactionSchema } from '../utils/validationSchema.js';
import { createCategory, getCategories } from '../services/category.service.js';
import SubmitButton from './ui/SubmitButton.vue';
import { debounce, getCurrentDate } from '../utils/helpers.js';
import { storeTransaction } from '../services/transaction.service.js';
import { ElMessage } from 'element-plus';
import { ArrowTrendingDownIcon, ArrowTrendingUpIcon, ArrowsRightLeftIcon, BanknotesIcon, CalendarDaysIcon, PencilSquareIcon, TagIcon } from '@heroicons/vue/24/outline';
import { Check, Close } from '@element-plus/icons-vue'
import useCategoryStore from '../stores/categoryStore.js';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    editMode: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close-dialog', 'transaction-saved']);


const saveTransactionLoader = ref(false);
const transactionAmount = ref(0);
const transactionType = ref('expense');

const initialValues = {
    type: 'expense',
    amount: 0,
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
})

const categories = computed(() => {
    return transactionTypeForCategory.value === 'expense' ? categoryStore.expenseCategories : categoryStore.incomeCategories;
})


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
            console.log('fetchCategories chala api call keliye')
        }
    }
    catch (e) {
        handleError(e);
    }
    finally {
        categoriesLoader.value = false;
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

const transactionTypeChanged = (type) => {
    if (type !== 'expense' && type !== 'income') return;
    transactionTypeForCategory.value = type;
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

const handleFormSubmit = () => {
    formRef.value?.$el?.requestSubmit();
}

const saveTransaction = async (formData, { resetForm }) => {
    try {
        if (!formData) return;
        saveTransactionLoader.value = true;
        const response = await storeTransaction(formData);
        if (response.data.success) {
            resetForm({ value: initialValues })
            closeDialog();
            ElMessage({
                type: 'success',
                message: response.data.message
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


const closeDialog = () => {
    emit('close-dialog');
}

watch(() => props.visible, (val) => {
    if (val) {
        fetchCategories();
    }
})


</script>

<template>
    <el-dialog v-model="dialogVisible" align-center :width="700">
        <template #header>
            <h2 class="text-md sm:text-lg font-semibold" v-if="!editMode">Add Transaction</h2>
            <h2 class="text-md sm:text-lg font-semibold" v-else>Edit Transaction</h2>
            <p class="text-xs" v-if="!editMode">Record your income or expenses with the details below.</p>
            <p class="text-xs" v-else>Update the transaction details below.</p>
        </template>
        <div v-loading="loading">
            <Form ref="formRef" @submit="saveTransaction" :validation-schema="transactionSchema"
                :initial-values="initialValues" v-slot="{ resetForm }">
                <el-row :gutter="20">
                    <el-col :span="24">
                        <Field name="type" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5">
                                        <ArrowsRightLeftIcon class="h-3.5 w-3.5" />
                                        Transaction type
                                    </span>
                                </template>
                                <el-radio-group :model-value="field.value" @update:model-value="handleChange"
                                    @change="debouncedTransactionTypeChanged(field.value)"
                                    class="!grid w-full grid-cols-2 gap-3">
                                    <el-radio value="expense"
                                        class="!m-0 !mr-0 !flex !h-auto !w-full !items-center !whitespace-normal rounded-lg border px-3 py-2.5"
                                        :class="field.value === 'expense'
                                            ? '!border-(--primary-green) bg-(--el-color-primary-light-9)'
                                            : '!border-gray-200 bg-white hover:!border-gray-300'">
                                        <span class="flex min-w-0 items-center gap-2.5">
                                            <span
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md"
                                                :class="field.value === 'expense'
                                                    ? 'bg-(--primary-green) text-white'
                                                    : 'bg-(--bg-light-gray) text-(--secondary-gray)'">
                                                <ArrowTrendingDownIcon class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 leading-tight">
                                                <span class="block text-sm font-semibold text-(--text-charcoal)">Expense</span>
                                                <span class="block text-[11px] font-normal text-(--secondary-gray)">Money out</span>
                                            </span>
                                        </span>
                                    </el-radio>
                                    <el-radio value="income"
                                        class="!m-0 !mr-0 !flex !h-auto !w-full !items-center !whitespace-normal rounded-lg border px-3 py-2.5"
                                        :class="field.value === 'income'
                                            ? '!border-(--primary-green) bg-(--el-color-primary-light-9)'
                                            : '!border-gray-200 bg-white hover:!border-gray-300'">
                                        <span class="flex min-w-0 items-center gap-2.5">
                                            <span
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md"
                                                :class="field.value === 'income'
                                                    ? 'bg-(--primary-green) text-white'
                                                    : 'bg-(--bg-light-gray) text-(--secondary-gray)'">
                                                <ArrowTrendingUpIcon class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 leading-tight">
                                                <span class="block text-sm font-semibold text-(--text-charcoal)">Income</span>
                                                <span class="block text-[11px] font-normal text-(--secondary-gray)">Money in</span>
                                            </span>
                                        </span>
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :sm="12">
                        <Field name="amount" v-slot="{ field, handleChange, errorMessage }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5">
                                        <BanknotesIcon class="h-3.5 w-3.5" />
                                        Amount
                                    </span>
                                </template>
                                <el-input-number class="!w-full" :model-value="field.value"
                                    @update:model-value="handleChange" :precision="2" :step="1" :formatter="formatted"
                                    :parser="parsed" :max="100000000" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :sm="12">
                        <Field name="transaction_date" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5">
                                        <CalendarDaysIcon class="h-3.5 w-3.5" />
                                        Date
                                    </span>
                                </template>
                                <el-date-picker class="!w-full [&_.el-input__wrapper]:w-full" :model-value="field.value"
                                    @update:model-value="handleChange" type="date" placeholder="Pick a day"
                                    :shortcuts="shortcuts" value-format="YYYY-MM-DD" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :span="24">
                        <Field name="note" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5">
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
                            <el-form-item label-position="top" :error="errorMessage" class="!mb-8">
                                <template #label>
                                    <span class="inline-flex items-center gap-1.5">
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
                                <div v-else class="h-[88px] overflow-y-auto">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <el-check-tag type="primary" :checked="field.value === category.id"
                                            @change="handleChange(field.value === category.id ? null : category.id)"
                                            v-for="category in categories" :key="category.id"
                                            class="!rounded-full !px-3 !py-1 !text-xs">
                                            {{ category.name }}
                                        </el-check-tag>
                                        <div>
                                            <div v-if="newCategoryInputVisible" class="flex items-center gap-2">
                                                <el-input ref="newCategoryInputRef" v-model="newCategoryVal"
                                                    class="w-36" size="small" placeholder="Category name"
                                                    @keyup.enter="handleNewCategory"
                                                    :disabled="newCategoryInputLoader" />
                                                <div class="flex items-center gap-1">
                                                    <el-button type="success" size="small" :icon="Check" circle
                                                        @click="handleNewCategory"
                                                        :disabled="newCategoryInputLoader" />
                                                    <el-button type="danger" size="small" :icon="Close" circle
                                                        @click="clearCategoryVal"
                                                        :disabled="newCategoryInputLoader" />
                                                </div>
                                            </div>
                                            <el-button v-else size="small" @click="showNewCategoryInput">
                                                + New
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>
                        </Field>
                    </el-col>
                </el-row>
            </Form>
        </div>
        <template #footer>
            <div>
                <el-button @click="closeDialog">Cancel</el-button>
                <SubmitButton text="Save" @click="handleFormSubmit" :is-loading="saveTransactionLoader" />
            </div>
        </template>
    </el-dialog>
</template>
