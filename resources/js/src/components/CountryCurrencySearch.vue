<script setup>
/*
This component is used to search ang select country name to set the desired user's currency for the app.
*/
import { getAllISOCodes } from "iso-country-currency";
import { onMounted, ref } from "vue";
import { capitalizeEachWord } from "../utils/helpers";

const countryName = ref("");
const allCountries = ref([]);

const emits = defineEmits(['selected-country']);

const querySearch = (searchValue, cb) => {
    const suggestions = searchValue ? allCountries.value.filter((item) => {
        return item.value.indexOf(capitalizeEachWord(searchValue)) === 0;
    }) : allCountries.value;

    cb(suggestions);
};

const handleSelect = (country) => {
    emits('selected-country', country)
};

onMounted(() => {
    allCountries.value = getAllISOCodes().map((country) => {
        return {
            ...country,
            value: country.countryName
        }
    });
})

</script>

<template>
    <div>
        <el-autocomplete
            v-model="countryName"
            :fetch-suggestions="querySearch"
            :trigger-on-focus="false"
            class="w-50"
            placeholder="Country name"
            @select="handleSelect"
        />
    </div>
</template>
