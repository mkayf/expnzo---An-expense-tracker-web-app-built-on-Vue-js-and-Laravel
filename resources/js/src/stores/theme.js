import { defineStore } from "pinia";

export const THEME_STORAGE_KEY = "expnzo-theme";

const applyThemeClass = (theme) => {
    const root = document.documentElement;
    root.classList.toggle("dark", theme === "dark");
    root.style.colorScheme = theme;
};

const useThemeStore = defineStore("theme", {
    state: () => ({
        theme: "light",
    }),
    getters: {
        isDark: (state) => state.theme === "dark",
    },
    actions: {
        init() {
            const stored = localStorage.getItem(THEME_STORAGE_KEY);
            this.theme = stored === "dark" ? "dark" : "light";
            applyThemeClass(this.theme);
        },
        setTheme(theme) {
            this.theme = theme === "dark" ? "dark" : "light";
            localStorage.setItem(THEME_STORAGE_KEY, this.theme);
            applyThemeClass(this.theme);
        },
        toggle() {
            this.setTheme(this.isDark ? "light" : "dark");
        },
    },
});

export default useThemeStore;
