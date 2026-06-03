import { createRouter, createWebHistory } from "vue-router";
import authRoutes from "./authRoutes";
import appRoutes from "./appRoutes";
import publicRoutes from "./publicRoutes";
import useAuthStore from "../stores/auth";

const routes = [
    {
        path: "/",
        component: () => import("../layouts/DefaultLayout.vue"),
        children: publicRoutes,
    },
    {
        path: "/app",
        component: () => import("../layouts/DashboardLayout.vue"),
        redirect: "/app/dashboard",
        children: appRoutes,
        meta: {
            breadcrumb: "Dashboard",
        },
    },
    {
        path: "/auth",
        component: () => import("../layouts/AuthLayout.vue"),
        children: authRoutes,
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: () => import("../views/NotFound.vue"),
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

// // router.beforeEach((to, from, next) => {
// //     const authStore = useAuthStore();
// //     const isAuthenticated = authStore.isAuthenticated;
// //     // Email verification check
// //     if(isAuthenticated){
// //         const isVerified = authStore.user?.email_verified;
// //         if(!isVerified && !to.name === 'VerifyEmail'){
// //             next({name: 'VerifyEmail'});
// //         }

// //         if(isVerified && to.name === 'VerifyEmail'){
// //             next({name: 'Dashboard'});
// //         }
// //     }

// //     if (to.matched.some((route) => route.meta.requiresAuth)) {
// //         if (!isAuthenticated) {
// //             next({
// //                 name: "Login",
// //             });
// //         }
// //         else {
// //             next();
// //         }
// //     } else if (to.matched.some((route) => route.meta.requiresAuth === false)) {
// //         if (isAuthenticated) {
// //             next({
// //                 name: "Dashboard",
// //             });
// //         } else {
// //             next();
// //         }
// //     } else {
// //         next();
// //     }
// });

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    const isVerified = authStore.user?.email_verified ?? false;

    if (to.matched.some((route) => route.meta.requiresAuth)) {
        if (!isAuthenticated) {
            return next({ name: "Login" });
        }

        if (!isVerified && to.name !== "VerifyEmail") {
            return next({ name: "VerifyEmail" });
        }

        if (isVerified && to.name === "VerifyEmail") {
            return next({ name: "Dashboard" });
        }

        return next();
    }

    if (to.matched.some((route) => route.meta.requiresAuth === false)) {
        if (isAuthenticated) {
            return isVerified ? next({ name: "Dashboard" }) : next({ name: "VerifyEmail" });
        }
        return next();
    }

    return next();
});
