import { createRouter, createWebHistory } from "vue-router";
import authRoutes from "./authRoutes";

const routes = [
      ...authRoutes,
];

export const router = createRouter({
    history: createWebHistory(),
    routes
})