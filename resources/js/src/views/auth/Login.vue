<script setup>
import darkLogo from "@/assets/logo/expnzo-dark-logo.png";
import lightLogo from "@/assets/logo/expnzo-light-logo.png";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import { Field, Form } from "vee-validate";
import { loginSchema } from "../../utils/validationSchema";
import { onMounted, ref } from "vue";
import { login } from "../../services/auth.service.js";
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import { useRouter, useRoute } from "vue-router";
import useAuthStore from "../../stores/auth";
import handleError from "../../utils/handleError";
import GoogleAuthButton from "../../components/ui/GoogleAuthButton.vue";
import useThemeStore from "../../stores/theme";

let loading = ref(false);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const loginUser = async (formData) => {
    try {
        loading.value = true;
        const res = await login(formData);
        if (res.data.success) {
            await authStore.fetchCurrentUser();
            router.push('/app/dashboard');
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if(window.__FLASH__?.auth_error){
        ElMessage({
            type: 'error',
            message: window.__FLASH__.auth_error
        })
        delete window.__FLASH__.auth_error;
    }
})

</script>

<template>
    <div class="bg-[var(--app-surface)] p-6 rounded-2xl shadow-lg w-full max-w-80 dark:border dark:border-[var(--el-border-color)]">
        <img :src="themeStore.isDark ? darkLogo : lightLogo" alt="Expnzo"
            class="h-[40px] w-[160px] object-contain mx-auto mb-2" />
        <Form class="auth-form" @submit="loginUser" :validation-schema="loginSchema">
            <h2 class="text-xl mb-4 text-center">Login to your account</h2>

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

            <Field name="remember_me" v-slot="{ field, errorMessage, handleChange }">
                <el-form-item :error="errorMessage">
                    <el-checkbox
                        label="Remember me"
                        size="small"
                        :model-value="field.value"
                        @update:model-value="handleChange"
                    />
                </el-form-item>
            </Field>

            <div class="flex justify-end mb-3">
                <small>
                    Don't have an account?
                    <RouterLink to="/auth/register" class="text-[var(--primary-green)]">Create one</RouterLink>
                </small>
            </div>

            <el-form-item class="mb-3">
                <SubmitButton :is-loading="loading" text="Login" classes="w-full" />
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