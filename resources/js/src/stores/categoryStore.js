import { defineStore } from "pinia";

const useCategoryStore = defineStore("category", {
    state: () => {
        return {
            expenseCategories: [],
            incomeCategories: []
        }
    },

    actions: {
        setCategories(type, categories){
            if(type === 'expense'){
                this.expenseCategories = categories
            }  

            if(type === 'income'){
                this.incomeCategories = categories;
            }
        }
    }

});

export default useCategoryStore;