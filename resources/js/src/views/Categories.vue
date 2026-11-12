<script setup>
import { computed, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import DataTable from '../components/DataTable.vue';
import {
    bulkDeleteCategories,
    createCategory,
    deleteCategory,
    exportCategories,
    exportCategoriesPdf,
    listCategories,
    updateCategory,
} from '../services/category.service.js';
import useCategoryStore from '../stores/categoryStore.js';
import handleError from '../utils/handleError.js';
import { formatDate } from '../utils/helpers.js';

const categoryStore = useCategoryStore();
const apiService = listCategories;
const excelExportService = exportCategories;
const pdfExportService = exportCategoriesPdf;
const refresh = ref(0);
const isCategoryDialogOpen = ref(false);
const isSaving = ref(false);
const categoryName = ref('');
const categoryType = ref('expense');
const editingCategoryId = ref(null);
const deletingCategoryId = ref(null);
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
        label: 'Category',
        mobile: 'title',
        prop: 'name',
        sortable: true,
    },
    {
        label: 'Type',
        mobile: 'badge',
        prop: 'type',
        sortable: true,
    },
    {
        label: 'Created At',
        mobile: 'detail',
        prop: 'created_at',
        sortable: true,
    },
    {
        label: 'Actions',
        mobile: 'actions',
        prop: 'actions',
        sortable: false,
    },
];

const exportColumns = [
    { label: 'Category ID', prop: 'id' },
    { label: 'Category', prop: 'name' },
    { label: 'Type', prop: 'type' },
    { label: 'Created At', prop: 'created_at' },
    { label: 'Updated At', prop: 'updated_at' },
];

const openCategoryDialog = (category = null) => {
    editingCategoryId.value = category?.id ?? null;
    categoryName.value = category?.name ?? '';
    categoryType.value = category?.type ?? 'expense';
    isCategoryDialogOpen.value = true;
};

const saveCategory = async () => {
    const name = categoryName.value.trim();
    if (name.length < 5 || name.length > 50) {
        ElMessage.warning('Category name must be between 5 and 50 characters');
        return;
    }

    isSaving.value = true;
    try {
        const response = editingCategoryId.value
            ? await updateCategory(editingCategoryId.value, { name, type: categoryType.value })
            : await createCategory({ name, type: categoryType.value });
        const category = response.data?.category;
        if (!response.data?.success || !category) {
            throw new Error(response.data?.message || 'Failed to save category');
        }

        if (editingCategoryId.value) {
            categoryStore.updateCategory(category);
        } else {
            categoryStore.addCategory(category.type, category);
        }
        ElMessage.success(response.data.message || 'Category saved successfully');
        isCategoryDialogOpen.value = false;
        refresh.value++;
    } catch (error) {
        handleError(error);
    } finally {
        isSaving.value = false;
    }
};

const handleDeleteCategory = async (category) => {
    if (deletingCategoryId.value !== null || isBulkDeleting.value) return;

    try {
        await ElMessageBox.confirm(
            `Delete "${category.name}"? Categories used by transactions cannot be deleted.`,
            'Delete category',
            {
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                type: 'warning',
            },
        );

        deletingCategoryId.value = category.id;
        const response = await deleteCategory(category.id);
        if (!response.data?.success) {
            throw new Error(response.data?.message || 'Failed to delete category');
        }

        categoryStore.removeCategories([category.id]);
        ElMessage.success(response.data.message || 'Category deleted successfully');
        refresh.value++;
    } catch (error) {
        if (error === 'cancel' || error === 'close') return;
        handleError(error);
    } finally {
        deletingCategoryId.value = null;
    }
};

const handleBulkAction = async ({ key, ids }) => {
    if (key !== 'delete' || ids.length === 0 || isBulkDeleting.value || deletingCategoryId.value !== null) return;

    try {
        await ElMessageBox.confirm(
            `Delete ${ids.length} selected categor${ids.length === 1 ? 'y' : 'ies'}? Categories used by transactions cannot be deleted.`,
            'Delete selected categories',
            {
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                type: 'warning',
            },
        );

        isBulkDeleting.value = true;
        const response = await bulkDeleteCategories(ids);
        if (!response.data?.success) {
            throw new Error(response.data?.message || 'Failed to delete selected categories');
        }

        categoryStore.removeCategories(ids);
        ElMessage.success(response.data.message || `${response.data.deleted_count} categories deleted successfully`);
        refresh.value++;
    } catch (error) {
        if (error === 'cancel' || error === 'close') return;
        handleError(error);
    } finally {
        isBulkDeleting.value = false;
    }
};
</script>

<template>
    <h1 class="mb-4 text-2xl font-semibold">Categories</h1>

    <DataTable
        :columns="columns"
        :api-service="apiService"
        :default-sort="{ prop: 'name', order: 'ascending' }"
        :export-excel="true"
        :export-service="excelExportService"
        :export-columns="exportColumns"
        excel-file-name="categories.xlsx"
        :exportPDF="true"
        :pdf-export-service="pdfExportService"
        :pdf-export-columns="exportColumns"
        pdf-file-name="categories.pdf"
        :bulk-actions="bulkActions"
        :update="refresh"
        @bulk-action="handleBulkAction"
    >
        <template #addons>
            <el-button size="small" type="primary" @click="openCategoryDialog()">
                Add category
            </el-button>
        </template>
        <template #type="{ row }">
            <el-tag effect="light" round :type="row.type === 'expense' ? 'warning' : 'primary'">
                {{ row.type }}
            </el-tag>
        </template>
        <template #created_at="{ row }">
            {{ formatDate(row.created_at) || '-' }}
        </template>
        <template #actions="{ row }">
            <el-button
                :icon="PencilSquareIcon"
                size="small"
                type="primary"
                circle
                plain
                :disabled="deletingCategoryId !== null || isBulkDeleting"
                :aria-label="`Edit ${row.name}`"
                @click="openCategoryDialog(row)"
            />
            <el-button
                :icon="TrashIcon"
                size="small"
                type="danger"
                circle
                plain
                :loading="deletingCategoryId === row.id"
                :disabled="(deletingCategoryId !== null && deletingCategoryId !== row.id) || isBulkDeleting"
                :aria-label="`Delete ${row.name}`"
                @click="handleDeleteCategory(row)"
            />
        </template>
    </DataTable>

    <el-dialog
        v-model="isCategoryDialogOpen"
        :title="editingCategoryId ? 'Edit category' : 'Add category'"
        width="420px"
        :close-on-click-modal="!isSaving"
        :close-on-press-escape="!isSaving"
    >
        <el-form label-position="top" @submit.prevent="saveCategory">
            <el-form-item label="Name" required>
                <el-input
                    v-model="categoryName"
                    maxlength="50"
                    minlength="5"
                    show-word-limit
                    placeholder="Enter a category name"
                    @keyup.enter="saveCategory"
                />
            </el-form-item>
            <el-form-item label="Type" required>
                <el-select v-model="categoryType" class="w-full">
                    <el-option label="Expense" value="expense" />
                    <el-option label="Income" value="income" />
                </el-select>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="flex justify-end gap-2">
                <el-button :disabled="isSaving" @click="isCategoryDialogOpen = false">Cancel</el-button>
                <el-button type="primary" :loading="isSaving" @click="saveCategory">
                    {{ editingCategoryId ? 'Save changes' : 'Save category' }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>
