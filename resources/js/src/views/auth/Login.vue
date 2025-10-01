<script setup>
import logo from "@/assets/logo/logo.png";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import { Field, Form } from "vee-validate";
import { loginSchema } from "../../utils/validationSchema";

const login = (values) => {
    console.log(values);
}

</script>

<template>
    <div class="bg-white p-6 rounded-2xl shadow-xl w-80">
        <img :src="logo" alt="Logo" class="h-[40px] mx-auto my-3">
        <Form @submit="login" :validation-schema="loginSchema">
            <h2 class="text-xl my-3 text-center ">Login to your account</h2> 
            <div class="mb-4">
                <Field name="email" v-slot="{field, errorMessage, handleChange}">
                    <el-form-item label="Email" :error="errorMessage">
                        <el-input type="email" :model-value="field.value" @update:model-value="handleChange" placeholder="Enter your email" />
                    </el-form-item>
                </Field>
            </div>
            <div class="mb-4">
                <Field name="password" v-slot="{field, errorMessage, handleChange}">
                    <el-form-item label="Password" :error="errorMessage">
                        <el-input type="password" :model-value="field.value" @update:model-value="handleChange" placeholder="Enter your password" show-password />
                    </el-form-item>
                </Field>
            </div>
            <div class="mb-4">
                <Field name="remember_me" v-slot="{field, errorMessage, handleChange}">
                    <el-form-item :error="errorMessage">
                        <el-checkbox label="Remember me" size="small" :model-value="field.value" @update:model-value="handleChange" />
                    </el-form-item>
                </Field>
            </div>
            <div class="flex justify-end my-3">
                <small>Don't have an account? <RouterLink to="/register" class="text-[var(--primary-green)]">Create one</RouterLink></small>
            </div>
            <el-form-item>
                <SubmitButton text="Create" classes="w-full" />
            </el-form-item>
        </Form>
    </div>
</template>