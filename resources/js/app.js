import "./bootstrap";
import { createApp } from "vue";
import App from "./src/App.vue";
import { router } from "./src/router";
import { createPinia } from "pinia";
import useAuthStore from "./src/stores/auth";
import { ElLoading } from "element-plus";
import "element-plus/es/components/loading/style/css";
import "./src/assets/logo/logo.png";
import useThemeStore from "./src/stores/theme";
window.Apex = {
    chart: {
        fontFamily: "Montserrat, sans-serif",
        redrawOnParentResize: false,
    },
    dataLabels: {
        style: {
            fontWeight: 600,
        },
    },
    xaxis: {
        labels: {
            style: { fontWeight: 600 },
        },
    },
    yaxis: {
        labels: {
            style: { fontWeight: 600 },
        },
    },
    legend: {
        fontWeight: 600,
    },
    tooltip: {
        style: {
            fontWeight: 600,
        },
    },
};

async function initializeApp() {
    const isDark = document.documentElement.classList.contains("dark");
    const loading = ElLoading.service({
        lock: true,
        background: isDark ? "rgba(13, 18, 16, 0.94)" : "white",
        customClass: "app-loader",
    });

    const app = createApp(App);
    const pinia = createPinia();
    app.use(pinia);

    useThemeStore(pinia).init();
    const authStore = useAuthStore(pinia);

    try {
        await authStore.fetchCurrentUser();
    } catch (error) {
        console.error("Initialization failed:", error);
    }

    app.use(router);

    app.mount("#app");

    loading.close();
}

initializeApp();
