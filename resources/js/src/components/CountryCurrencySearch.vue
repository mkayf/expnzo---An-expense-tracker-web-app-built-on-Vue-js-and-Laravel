<script setup>
/*
This component is used to search ang select country name to set the desired user's currency for the app.
*/
import { getAllISOCodes, getParamByISO, getParamByParam } from "iso-country-currency";
import { onMounted, ref } from "vue";
import { capitalizeEachWord } from "../utils/helpers";
import useAuthStore from "../stores/auth";

const authStore = useAuthStore();
const defaultCurrencyIso = authStore.user.preferences.currency_iso;
const defaultCountry = ref(null);
const countryName = ref("");
const allCountries = ref([]);

const emits = defineEmits(["selected-country"]);
const props = defineProps({
    field: {
        required: true,
    },
    handleChange: {
        required: true,
    },
});



const querySearch = (searchValue, cb) => {
    const suggestions = searchValue
        ? allCountries.value.filter((item) => {
              return item.value.indexOf(capitalizeEachWord(searchValue)) === 0;
          })
        : allCountries.value;
    cb(suggestions);
};

const handleSelect = (country) => {
    emits("selected-country", country);
};

onMounted(() => {
    allCountries.value = getAllISOCodes().map((country) => {
        return {
            ...country,
            value: country.countryName,
        };
    });

    const country = getParamByISO(defaultCurrencyIso, 'countryName');

    emits('selected-country', allCountries.value.find((item) => item.countryName === country));

    props.handleChange(country);
});
</script>

<template>
    <div>
        <el-autocomplete
            :fetch-suggestions="querySearch"
            :trigger-on-focus="false"
            class="w-50"
            placeholder="Country name"
            @select="handleSelect"
            :model-value="field.value"
            @update:model-value="handleChange"
        />
    </div>
</template>
