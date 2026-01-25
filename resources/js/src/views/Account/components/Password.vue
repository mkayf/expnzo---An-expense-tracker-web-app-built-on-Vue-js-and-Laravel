<script setup>
import { Form, Field} from "vee-validate";
import { ref } from "vue";
import SubmitButton from "../../../components/ui/SubmitButton.vue";
import { changePasswordSchema } from "../../../utils/validationSchema";
import { changePassword } from "../../../services/auth";
import handleError from "../../../utils/handleError";
import { ElMessage } from "element-plus";
import 'element-plus/es/components/message/style/css'

const buttonLoader = ref(false);

const changeUserPassword = async (formData) => {
    try{
        buttonLoader.value = true;
        const response = await changePassword(formData);
        if(response.data.success){
            ElMessage({
                type: "success",
                message:
                    response.data.message || "Password changed successfuly",
            });
            return;
        }

       throw new Error(response.data?.message || "Something went wrong");

    }
    catch(e){
        handleError(e);
    }
    finally{
        buttonLoader.value = false;
    }
}

</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Change Password</h4>
        <Form @submit="changeUserPassword" :validation-schema="changePasswordSchema" class="mt-4">
                <el-row :gutter="20" class="flex flex-col">
                    <el-col :md="12" class="mb-4">
                        <Field name="current_password" v-slot="{field, handleChange, errorMessage}">
                            <el-form-item label="Current Password" :error="errorMessage">
                                <el-input type="password" 
                                show-password
                                :model-value="field.value"
                                @update:model-value="handleChange"
                                placeholder="Enter current password"
                                 />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :md="12" class="mb-4">
                            <Field name="new_password" v-slot="{field, handleChange, errorMessage}">
                            <el-form-item label="New Password" :error="errorMessage">
                                <el-input type="password" 
                                show-password
                                :model-value="field.value"
                                @update:model-value="handleChange"
                                placeholder="Enter new password"
                                 />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :md="12">
                            <Field name="new_password_confirmation" v-slot="{field, handleChange, errorMessage}">
                            <el-form-item label="Confirm Password" :error="errorMessage">
                                <el-input type="password" 
                                show-password
                                :model-value="field.value"
                                @update:model-value="handleChange"
                                placeholder="Enter password again"
                                 />
                            </el-form-item>
                        </Field>
                    </el-col>
                </el-row>
                <div>
                    <SubmitButton classes="md:!w-[100px] mt-4" text="Change" :isLoading="buttonLoader" />
                </div>
            </Form>
    </div>
</template>
