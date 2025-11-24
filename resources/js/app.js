import "./bootstrap";
import { createApp } from "vue";
import App from "./src/App.vue";
import { router } from "./src/router/";
import { createPinia } from "pinia";
import useAuthStore from "./src/stores/auth";
import { ElLoading } from "element-plus";
import "element-plus/es/components/loading/style/css";
import "./src/assets/logo/logo.png";

async function initializeApp() {
    const loading = ElLoading.service({
        lock: true,
        background: "white",
        customClass: "app-loader",
    });

    const app = createApp(App);
    const pinia = createPinia();
    app.use(pinia);

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
