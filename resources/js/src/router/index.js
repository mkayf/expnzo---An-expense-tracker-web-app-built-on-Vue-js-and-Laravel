import { createRouter, createWebHistory } from "vue-router";
import authRoutes from "./authRoutes";
import appRoutes from "./appRoutes";
import useAuthStore from "../stores/auth";

const routes = [
    {
        path: "/",
        component: () => import("../layouts/DefaultLayout.vue"),
        children: appRoutes,
    },
    {
        path: "/auth",
        component: () => import("../layouts/AuthLayout.vue"),
        children: authRoutes,
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    if (to.matched.some((route) => route.meta.requiresAuth)) {
        if (!isAuthenticated) {
            next({
                name: "Login",
                query: { redirect: to.fullPath },
            });
        } else {
            next();
        }
    } else if (to.matched.some((route) => route.meta.requiresAuth === false)) {
        if (isAuthenticated) {
            next({
                name: "Dashboard",
            });
        } else {
            next();
        }
    } else {
        next();
    }
});
