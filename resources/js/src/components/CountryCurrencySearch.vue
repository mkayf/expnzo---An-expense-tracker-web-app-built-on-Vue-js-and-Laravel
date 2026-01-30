<script setup>
import { getAllISOCodes } from "iso-country-currency";
import { onMounted, ref } from "vue";
import { capitalizeEachWord } from "../utils/helpers";
/*
This component is used to search ang select country name to set the desired user's currency for the app.
*/


const countryName = ref("");
const allCountries = ref([]);
const selectedCountry = ref(null);

const querySearch = (searchValue, callback) => {
    const suggestions = allCountries.value.filter((item) => {
        return item.countryName?.includes(capitalizeEachWord(searchValue)) ? item.countryName : null;        
    })
};

const handleSelect = () => {};


onMounted(() => {
    allCountries.value = getAllISOCodes();
})

</script>

<template>
    <div>
        <el-autocomplete
            v-model="countryName"
            :fetch-suggestions="querySearch"
            :trigger-on-focus="false"
            clearable
            class="w-50"
            placeholder="Country name"
            @select="handleSelect"
        />
    </div>
</template>
