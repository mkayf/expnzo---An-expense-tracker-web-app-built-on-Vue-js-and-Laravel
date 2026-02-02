<script setup>
import { ref } from "vue";
import CountryCurrencySearch from "../../../components/CountryCurrencySearch.vue";
import SubmitButton from "../../../components/ui/SubmitButton.vue";
import { Form } from "vee-validate";

const selectedCountry = ref(null);

const getCountry = (country) => {
    selectedCountry.value = country;
    console.log(selectedCountry.value);
};

const savePreferences = (formData) => {
    console.log(formData);
};

</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Preferences</h4>
        <Form @submit="savePreferences">
            <el-row class="flex flex-col">
                <el-col :md="12">
                    <el-form-item
                        label="Select the currency that you want to use"
                        label-position="top"
                    >
                        <div class="flex items-center gap-4 mt-2">
                            <CountryCurrencySearch
                            @selected-country="getCountry"
                            />
                            <span
                                v-if="selectedCountry && selectedCountry.currency"
                                class="font-semibold text-xl bg-gray-100 p-2 rounded-lg"
                                >{{ selectedCountry.currency }}</span>    
                        </div>
                    </el-form-item>
                </el-col>
                <div>
                    <SubmitButton text="Save" classes="md:!w-[100px] mt-4" />
                </div>
            </el-row>
        </Form>
    </div>
</template>
