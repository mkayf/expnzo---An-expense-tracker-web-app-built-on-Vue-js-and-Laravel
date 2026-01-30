<script setup>
import CountryCurrencySearch from "../../../components/CountryCurrencySearch.vue";
</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Preferences</h4>
        <el-row>
            <el-form>
                <el-form-item
                    label="Select the currency that you want to use"
                    label-position="top"
                >
                    <CountryCurrencySearch />
                </el-form-item>
            </el-form>
        </el-row>
    </div>
</template>
