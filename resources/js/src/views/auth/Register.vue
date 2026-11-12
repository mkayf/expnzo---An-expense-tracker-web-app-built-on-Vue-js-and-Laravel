<script setup>
import darkLogo from "@/assets/logo/expnzo-dark-logo.png";
import lightLogo from "@/assets/logo/expnzo-light-logo.png";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import { Form, Field } from "vee-validate";
import { registerSchema } from "../../utils/validationSchema";
import { register } from "../../services/auth.service.js";
import { ref } from "vue";
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import useAuthStore from "../../stores/auth";
import { useRouter } from "vue-router";
import GoogleAuthButton from "../../components/ui/GoogleAuthButton.vue";
import handleError from "../../utils/handleError";
import useThemeStore from "../../stores/theme";

let loading = ref(false);
const authStore = useAuthStore();
const router = useRouter();
const themeStore = useThemeStore();

const registerUser = async (formData) => {
    try {
        loading.value = true;
        const res = await register(formData);
        if (res.data.success) {
            ElMessage({
                message: res.data.message,
                type: "success",
            });
            await authStore.fetchCurrentUser();
            router.push("/auth/verify_email");
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="bg-[var(--app-surface)] p-6 rounded-2xl shadow-xl w-full max-w-80 dark:border dark:border-[var(--el-border-color)]">
        <img :src="themeStore.isDark ? darkLogo : lightLogo" alt="Expnzo"
            class="h-[40px] w-[160px] object-contain mx-auto mb-2" />
        <Form class="auth-form" @submit="registerUser" :validation-schema="registerSchema">
            <h2 class="text-xl mb-4 text-center">Create account</h2>

            <Field name="name" v-slot="{ field, errorMessage, handleChange }">
                <el-form-item label="Name" label-position="top" :error="errorMessage">
                    <el-input
                        type="text"
                        :model-value="field.value"
                        @update:model-value="handleChange"
                        placeholder="Enter your name"
                    />
                </el-form-item>
            </Field>

            <Field name="email" v-slot="{ field, errorMessage, handleChange }">
                <el-form-item label="Email" label-position="top" :error="errorMessage">
                    <el-input
                        type="email"
                        :model-value="field.value"
                        @update:model-value="handleChange"
                        placeholder="Enter your email"
                    />
                </el-form-item>
            </Field>

            <Field name="password" v-slot="{ field, errorMessage, handleChange }">
                <el-form-item label="Password" label-position="top" :error="errorMessage">
                    <el-input
                        type="password"
                        :model-value="field.value"
                        @update:model-value="handleChange"
                        placeholder="Enter your password"
                        show-password
                    />
                </el-form-item>
            </Field>

            <Field name="password_confirmation" v-slot="{ field, errorMessage, handleChange }">
                <el-form-item label="Confirm Password" label-position="top" :error="errorMessage">
                    <el-input
                        type="password"
                        :model-value="field.value"
                        @update:model-value="handleChange"
                        placeholder="Repeat your password"
                    />
                </el-form-item>
            </Field>

            <div class="flex justify-end mb-3">
                <small>
                    Already have an account?
                    <RouterLink to="/auth/login" class="text-[var(--primary-green)]">Login</RouterLink>
                </small>
            </div>

            <el-form-item class="mb-3">
                <SubmitButton :is-loading="loading" text="Create" classes="w-full" />
            </el-form-item>

            <GoogleAuthButton />
        </Form>
    </div>
</template>

<style scoped>
.auth-form :deep(.el-form-item) {
    margin-bottom: 22px;
}

.auth-form :deep(.el-form-item__label) {
    line-height: 22px;
    padding-bottom: 2px;
}

.auth-form :deep(.el-form-item__error) {
    font-size: 11px;
    line-height: 14px;
    padding-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}
</style>