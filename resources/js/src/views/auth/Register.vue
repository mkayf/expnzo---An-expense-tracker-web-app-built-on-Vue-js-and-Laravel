<script setup>
import logo from "@/assets/logo/logo.png";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import { Form, Field } from "vee-validate";
import { registerSchema } from "../../utils/validationSchema";
import { register } from "../../services/auth";
import { ref } from "vue";
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import useAuthStore from "../../stores/auth";
import { useRouter } from "vue-router";
import GoogleAuthButton from "../../components/ui/GoogleAuthButton.vue";

let loading = ref(false);
const authStore = useAuthStore();
const router = useRouter();

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
        ElMessage({
            message:
                "Something went wrong while creating your account. Try again, we’ll wait right here.",
            type: "error",
        });
        console.log(e.response?.data?.message);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="bg-white p-6 rounded-2xl shadow-xl w-80">
        <img :src="logo" alt="Logo" class="h-[40px] mx-auto my-3" />
        <Form @submit="registerUser" :validation-schema="registerSchema">
            <h2 class="text-xl my-3 text-center">Create account</h2>
            <div class="mb-4">
                <Field
                    name="name"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item
                        label="Name"
                        :error="errorMessage"
                        label-position="top"
                    >
                        <el-input
                            type="text"
                            :model-value="field.value"
                            @update:model-value="handleChange"
                            placeholder="Enter your name"
                        />
                    </el-form-item>
                </Field>
            </div>
            <div class="mb-4">
                <Field
                    name="email"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item
                        label="Email"
                        :error="errorMessage"
                        label-position="top"
                    >
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
                    <el-form-item
                        label="Password"
                        :error="errorMessage"
                        label-position="top"
                    >
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
                    name="password_confirmation"
                    v-slot="{ field, errorMessage, handleChange }"
                >
                    <el-form-item
                        label="Confirm Password"
                        :error="errorMessage"
                        label-position="top"
                    >
                        <el-input
                            type="password"
                            :model-value="field.value"
                            @update:model-value="handleChange"
                            placeholder="Repeat your password"
                        />
                    </el-form-item>
                </Field>
            </div>
            <div class="flex justify-end my-3">
                <small
                    >Already have an account?
                    <RouterLink
                        to="/auth/login"
                        class="text-[var(--primary-green)]"
                        >Login</RouterLink
                    ></small
                >
            </div>
            <el-form-item class="mb-3">
                <SubmitButton
                    :is-loading="loading"
                    text="Create"
                    classes="w-full"
                />
            </el-form-item>
            <GoogleAuthButton />
        </Form>
    </div>
</template>
