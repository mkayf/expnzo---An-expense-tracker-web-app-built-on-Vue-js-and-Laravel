<script setup>
import VueAvatarUpload from "@pkhadson/vue-avatar-upload";
import "@pkhadson/vue-avatar-upload/lib/style.css";
import Avatar from "../../../components/ui/Avatar.vue";
import useAuthStore from "../../../stores/auth";
import { CameraIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { handleError, ref } from "vue";
import { getCookie } from "../../../utils/helpers";
import { ElLoading, ElMessage, ElMessageBox } from "element-plus";
import "element-plus/es/components/message/style/css";
import "element-plus/es/components/message-box/style/css";
import "element-plus/es/components/loading/style/css";
import { deleteAvatar, saveProfileDetails } from "../../../services/user";
import { Form, Field} from "vee-validate";
import { profileSchema } from "../../../utils/validationSchema";
import SubmitButton from "../../../components/ui/SubmitButton.vue";

const authStore = useAuthStore();
const showAvatarModal = ref(false);
const uploadURL = `${import.meta.env.VITE_API_URL}/upload_avatar`;
let loadingInstance = null;
let buttonLoader = ref(false);

const uploadHeaders = {
    "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
};

const startLoading = (text = 'Loading') => {
    loadingInstance = ElLoading.service({
        lock: true,
        text,
        background: 'rgba(0,0,0,0.7)'
    });
}

const handleUploadSuccess = (res) => {
    const response = JSON.parse(res);
    if (response && response.success) {
        authStore.updateAvatar(response.url);
        authStore.user.has_custom_avatar = true;
        ElMessage({
            type: "success",
            message: response.message || "Profile updated successfully.",
        });
    }
};

const handleUploadError = (e) => {
    const error = JSON.parse(e);
    if (error) {
        ElMessage({
            type: "error",
            message: error.message
                ? error.message
                : "Failed to update profile image. Please try again",
        });
    }
};

const deleteProfileImage = async () => {
    try {
        startLoading('Deleting profile image');
        const response = await deleteAvatar();
        if (response.data.success) {
            authStore.updateAvatar(response.data.url);
            authStore.user.has_custom_avatar = false;
            ElMessage({
                type: "success",
                message:
                    response.data.message || "Profile image deleted successfully",
            });
            return;
        }

        throw new Error(response);
    } catch (e) {
        handleError(e);
    } finally{
        if(loadingInstance) loadingInstance.close();
    }
};

const confirmDelete = () => {
    ElMessageBox.confirm(
        "Do you want to delete your profile image?",
        "Confirm",
        {
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
            type: "warning",
        },
    ).then(() => {
            deleteProfileImage();      
        })
};

const saveProfile = async (formData) => {
    try{
        buttonLoader.value = true;
        const response = await saveProfileDetails(formData);
        if(response.data.success){
            authStore.user.name = formData.name;
            ElMessage({
                type: 'success',
                message: response.data.message || 'Profile saved'
            })
            return;
        }
        throw new Error(response);
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
        <h4 class="text-xl font-medium mb-4">Profile</h4>
        <div>
            <el-row class="flex items-center">
                <el-col :xs="24" :sm="10">
                    <Avatar class="flex justify-center" :size="150" :avatarURL="authStore.user?.avatar" />
                    <VueAvatarUpload
                        :headers="uploadHeaders"
                        lang="en"
                        v-show="showAvatarModal"
                        :url="uploadURL"
                        field="avatar"
                        :showPreview="false"
                        :avatar="authStore.user?.avatar"
                        @close="showAvatarModal = false"
                        @success="handleUploadSuccess"
                        @error="handleUploadError"
                    />
                </el-col>
                <el-col :xs="24" :sm="14">
                    <div class="flex justify-center lg:justify-end pl-0 md:pl-4 mt-4 lg:mt-0">
                        <el-button
                            @click="showAvatarModal = true"
                            type="primary"
                        >
                            <CameraIcon class="w-5 h-5 sm:mr-2" />
                            <span class="text-sm hidden sm:block">Upload new</span>
                        </el-button>
                        <el-button
                            class=",:mr-2"
                            plain
                            v-if="authStore.user?.has_custom_avatar"
                            @click="confirmDelete"
                        >
                            <TrashIcon class="w-5 h-5 sm:mr-2" />
                            <span class="text-sm hidden sm:block">Delete Avatar</span>
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <Form :initial-values="{ name: authStore.user.name || '' }" :validation-schema="profileSchema" @submit="saveProfile" class="mt-4">
                <el-row :gutter="20">
                    <el-col :xs="24" :md="12" class="mb-4">
                        <Field name="name" v-slot="{field, handleChange, errorMessage}">
                            <el-form-item label="Name" :error="errorMessage">
                                <el-input type="text" 
                                :model-value="field.value"
                                @update:model-value="handleChange"
                                placeholder="Enter name here"
                                 />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :md="12" class="">
                            <el-form-item label="Email (cannot be changed)">
                                <el-input type="email" v-model="authStore.user.email" readonly />
                            </el-form-item>
                    </el-col>
                </el-row>
                <div>
                    <SubmitButton classes="md:!w-[100px] mt-4" text="Save" :isLoading="buttonLoader" />
                </div>
            </Form>
        </div>
    </div>
</template>

<style>
.avatar-upload-actions .avatar-button.-salmon {
    background-color: var(--el-color-primary) !important;
}

.avatar-upload-root{
    z-index: 99;
}
</style>
