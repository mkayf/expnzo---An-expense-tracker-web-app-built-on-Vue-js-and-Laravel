<script setup>
import { computed, handleError, ref, watch } from 'vue';
import { Form, Field } from 'vee-validate';
import { transactionSchema } from '../utils/validationSchema.js';
import { getCategories } from '../services/category.service.js';
import SubmitButton from './ui/SubmitButton.vue';
import { getCurrentDate } from '../utils/helpers.js';
import { storeTransaction } from '../services/transaction.service.js';
import { ElMessage } from 'element-plus';


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

const categories = ref([]);
const categoriesLoader = ref(false);

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

const fetchCategories = async () => {
    try {
        categoriesLoader.value = true
        const response = await getCategories();
        if (response.data.success) {
            categories.value = response.data.data;
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

const saveTransaction = async (formData, {resetForm}) => {
    try {
        if (!formData) return;
        saveTransactionLoader.value = true;
        const response = await storeTransaction(formData);
        if (response.data.success) {
            resetForm({value: initialValues})
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
        <Form @submit="saveTransaction" :validation-schema="transactionSchema" :initial-values="initialValues" v-slot="{resetForm}">

            <div v-loading="loading">
                <el-row :gutter="20" class="items-center">
                    <el-col :xs="24" :sm="12">
                        <Field name="type" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label="Select transaction type" label-position="top" :error="errorMessage">
                                <el-radio-group :model-value="field.value" @update:model-value="handleChange">
                                    <el-radio value="expense" size="large" border>Expense</el-radio>
                                    <el-radio value="income" size="large" border>Income</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :sm="12">
                        <Field name="amount" v-slot="{ field, handleChange, errorMessage }">
                            <el-form-item label="Amount" label-position="top" :error="errorMessage">
                                <el-input-number style="width: 100%;" :model-value="field.value"
                                    @update:model-value="handleChange" :precision="2" :step="1" :formatter="formatted"
                                    :parser="parsed" :max="100000000" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :md="12">
                        <Field name="note" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label="Note" label-position="top" :error="errorMessage">
                                <el-input :model-value="field.value" @update:model-value="handleChange" type="textarea"
                                    placeholder="e.g. Lunch with friends" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :md="12">
                        <Field name="transaction_date" v-slot="{ field, errorMessage, handleChange }">
                            <el-form-item label="Date" label-position="top" :error="errorMessage">
                                <el-date-picker :model-value="field.value" @update:model-value="handleChange"
                                    type="date" placeholder="Pick a day" :shortcuts="shortcuts"
                                    value-format="YYYY-MM-DD" />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24">
                        <Field name="category_id" v-slot="{ field, handleChange, errorMessage }">
                            <el-form-item label="Categories" label-position="top" :error="errorMessage">
                                <el-skeleton animated v-if="categoriesLoader">
                                    <template #template>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />
                                            <el-skeleton-item variant="p" style="width: 20%;" />

                                        </div>
                                    </template>
                                </el-skeleton>
                                <div class="flex flex-wrap gap-2" v-else>
                                    <el-check-tag type="primary" :checked="field.value === category.id"
                                        @change="handleChange(field.value === category.id ? null : category.id)"
                                        v-for="category in categories" :key="category.id" class="!text-xs !px-2 !py-1">
                                        {{ category.name }}
                                    </el-check-tag>
                                </div>
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col>
                        <div class="flex justify-end">
                            <el-button @click="closeDialog">Cancel</el-button>
                            <SubmitButton text="Save" :is-loading="saveTransactionLoader" />
                        </div>
                    </el-col>
                </el-row>
            </div>
        </Form>
    </el-dialog>
</template>