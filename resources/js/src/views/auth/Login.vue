<script setup>
import logo from "@/assets/logo/logo.png";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import { Field, Form } from "vee-validate";
import { loginSchema } from "../../utils/validationSchema";
import { ref } from "vue";
import { login } from "../../services/auth";
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import { useRouter, useRoute } from "vue-router";
import useAuthStore from "../../stores/auth";
import handleError from "../../utils/handleError";
import GoogleAuthButton from "../../components/ui/GoogleAuthButton.vue";

let loading = ref(false);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const redirectTo = route.query.redirect ?? null;

const loginUser = async (formData) => {
    try {
        loading.value = true;
        const res = await login(formData);
        if (res.data.success) {
            ElMessage({
                message: res.data.message,
                type: "success",
            });
            await authStore.fetchCurrentUser();
            if(redirectTo){
              router.push(redirectTo);
            } else{
                router.push("/dashboard");
            }
        }
    } catch (e) {
        handleError(e);
        console.log(e);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="bg-white p-6 rounded-2xl shadow-lg w-80">
        <img :src="logo" alt="Logo" class="h-[40px] mx-auto my-3" />
        <Form @submit="loginUser" :validation-schema="loginSchema">
            <h2 class="text-xl my-3 text-center">Login to your account</h2>
            <div class="mb-4">
                <Field
                    name="email"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item label="Email" :error="errorMessage">
                        <el-input
                            type="email"
                            :model-value="field.value"
                            @update:model-value="handleChange"
                            placeholder="Enter your email"
                        />
                    </el-form-item>
                </Field>
            </div>
            <div class="mb-4">
                <Field
                    name="password"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item label="Password" :error="errorMessage">
                        <el-input
                            type="password"
                            :model-value="field.value"
                            @update:model-value="handleChange"
                            placeholder="Enter your password"
                            show-password
                        />
                    </el-form-item>
                </Field>
            </div>
            <div class="mb-4">
                <Field
                    name="remember_me"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item :error="errorMessage">
                        <el-checkbox
                            label="Remember me"
                            size="small"
                            :model-value="field.value"
                            @update:model-value="handleChange"
                        />
                    </el-form-item>
                </Field>
            </div>
            <div class="flex justify-end my-3">
                <small
                    >Don't have an account?
                    <RouterLink
                        to="/auth/register"
                        class="text-[var(--primary-green)]"
                        >Create one</RouterLink
                    ></small
                >
            </div>
            <el-form-item class="mb-3">
                <SubmitButton
                    :is-loading="loading"
                    text="Login"
                    classes="w-full"
                />
            </el-form-item>
            <GoogleAuthButton />
        </Form>
    </div>
</template>
