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
        },
        addCategory(type, category){
            if(type === 'expense'){
                this.expenseCategories.push(category);
            }
            
            if(type === 'income'){
                this.incomeCategories.push(category);
            }
        },
        updateCategory(category){
            const wasCached = this.expenseCategories.some((item) => item.id === category.id)
                || this.incomeCategories.some((item) => item.id === category.id);
            this.removeCategories([category.id]);
            if(wasCached){
                this.addCategory(category.type, category);
            }
        },
        removeCategories(ids){
            this.expenseCategories = this.expenseCategories.filter((category) => !ids.includes(category.id));
            this.incomeCategories = this.incomeCategories.filter((category) => !ids.includes(category.id));
        }
    }

});

export default useCategoryStore;