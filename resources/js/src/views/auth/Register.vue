<script setup>
import logo from '@/assets/logo/logo.png';
import SubmitButton from '../../components/ui/SubmitButton.vue';
import { Form, Field } from 'vee-validate';
import { registerSchema } from '../../utils/validationSchema';


const register = (values) => {
    console.log(values);
}

</script>

<template>


<div class="bg-white p-6 rounded-2xl shadow-xl w-80">
        <img :src="logo" alt="Logo" class="h-[40px] mx-auto my-3">
            <Form @submit="register" :validation-schema="registerSchema">
                <h2 class="text-xl my-3 text-center ">Create account</h2> 
                <div class="mb-4">
                    <Field name="name" v-slot="{field, errorMessage, handleChange}">
                        <el-form-item label="Name" :error="errorMessage">
                            <el-input type="text" :model-value="field.value" @update:model-value="handleChange" placeholder="Enter your name" />
                        </el-form-item>
                    </Field>
                </div>
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
                    <Field name="password_confirmation" v-slot="{field, errorMessage, handleChange}">
                        <el-form-item label="Confirm Password" :error="errorMessage">
                            <el-input type="password" :model-value="field.value" @update:model-value="handleChange" placeholder="Repeat your password" />
                        </el-form-item>
                    </Field>
                </div>
                <div class="flex justify-end my-3">
                    <small>Already have an account? <RouterLink to="/login" class="text-[var(--primary-green)]">Login</RouterLink></small>
                </div>
                <el-form-item>
                    <SubmitButton text="Create" classes="w-full" />
                </el-form-item>
            </Form>
    </div>
</template>
