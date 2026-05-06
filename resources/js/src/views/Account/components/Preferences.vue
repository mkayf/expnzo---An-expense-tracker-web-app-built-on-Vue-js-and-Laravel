<script setup>
import { handleError, onMounted, ref } from "vue";
import CountryCurrencySearch from "../../../components/CountryCurrencySearch.vue";
import SubmitButton from "../../../components/ui/SubmitButton.vue";
import { Field, Form } from "vee-validate";
import { countrySearchSchema } from "../../../utils/validationSchema";
import useAuthStore from "../../../stores/auth";
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import { saveUserPrefences } from "../../../services/user";

const authStore = useAuthStore();
const selectedCountry = ref(null);
const defaultCurrencyIso = ref(null);
const defaultCurrency = ref(null);
const buttonLoader = ref(false);

const getCountry = (country) => {
    selectedCountry.value = country;
};

const savePreferences = async () => {
    console.log(selectedCountry.value.iso === defaultCurrencyIso.value);
    if (selectedCountry.value.iso === defaultCurrencyIso.value) {
        ElMessage({
            type: "info",
            message: "You're already using this currency",
        });
        return;
    }

    try {
        buttonLoader.value = true;
        const response = await saveUserPrefences({
            currency: selectedCountry.value.currency,
            currency_iso: selectedCountry.value.iso
        });
        if (response.data.success) {
            authStore.user.preferences = response.data?.preferences;
            // directly updating default currency here
            defaultCurrency.value = response.data?.preferences?.currency;
            defaultCurrencyIso.value = response?.data?.preferences.currency_iso;
            ElMessage({
                type: "success",        
                message: response.data?.message,
            });
            return;
        }
        throw new Error(response);
    } catch (e) {
        handleError(e);
    } finally {
        buttonLoader.value = false;
    }
};

onMounted(() => {
    defaultCurrencyIso.value = authStore.user.preferences.currency_iso;
});
</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Preferences</h4>
        <Form
            @submit.prevent
            @keydown.enter.prevent
            :validation-schema="countrySearchSchema"
        >
            <el-row class="flex flex-col">
                <el-col :md="12">
                    <Field
                        name="country_name"
                        v-slot="{ field, handleChange, errorMessage }"
                    >
                        <el-form-item
                            label="Select the currency that you want to use"
                            label-position="top"
                            :error="errorMessage"
                        >
                            <div class="flex items-center gap-4 mt-2">
                                <CountryCurrencySearch
                                    @selected-country="getCountry"
                                    :field="field"
                                    :handleChange="handleChange"
                                />
                                <span
                                    v-if="
                                        (selectedCountry &&
                                            selectedCountry.currency) ||
                                        authStore.user.preferences.currency
                                    "
                                    class="font-semibold text-xl bg-gray-100 p-2 rounded-lg"
                                    >{{
                                        selectedCountry?.currency ??
                                        authStore.user.preferences.currency
                                    }}</span
                                >
                            </div>
                        </el-form-item>
                    </Field>
                </el-col>
                <div>
                    <SubmitButton
                        @submit-form="savePreferences"
                        :is-loading="buttonLoader"
                        text="Save"
                        classes="md:!w-[100px] mt-4"
                    />
                </div>
            </el-row>
        </Form>
    </div>
</template>
