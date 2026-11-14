<script setup>
import VueAvatarUpload from "@pkhadson/vue-avatar-upload";
import "@pkhadson/vue-avatar-upload/lib/style.css";
import Avatar from "../../../components/ui/Avatar.vue";
import useAuthStore from "../../../stores/auth";
import { CameraIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { handleError, onBeforeUnmount, onMounted, ref } from "vue";
import { getCookie } from "../../../utils/helpers";
import { ElLoading, ElMessage, ElMessageBox } from "element-plus";
import "element-plus/es/components/message/style/css";
import "element-plus/es/components/message-box/style/css";
import "element-plus/es/components/loading/style/css";
import { deleteAvatar, saveProfileDetails } from "../../../services/user.service.js";
import { Form, Field} from "vee-validate";
import { profileSchema } from "../../../utils/validationSchema";
import SubmitButton from "../../../components/ui/SubmitButton.vue";

const authStore = useAuthStore();
const showAvatarModal = ref(false);
const uploadURL = `${import.meta.env.VITE_API_URL  || '/api'}/upload_avatar`;
let loadingInstance = null;
let buttonLoader = ref(false);
const avatarUploadLoading = ref(false);
const hasSelectedNewAvatar = ref(false);
const avatarUploadKey = ref(0);
const avatarEditorSize = ref(300);

const updateAvatarEditorSize = () => {
    const size = Math.max(
        160,
        Math.min(300, window.innerWidth - 64, window.innerHeight - 190),
    );

    if (size !== avatarEditorSize.value) {
        avatarEditorSize.value = size;
        if (showAvatarModal.value && !hasSelectedNewAvatar.value && !avatarUploadLoading.value) {
            avatarUploadKey.value += 1;
        }
    }
};

onMounted(() => {
    updateAvatarEditorSize();
    window.addEventListener("resize", updateAvatarEditorSize);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", updateAvatarEditorSize);
});

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

const openAvatarModal = () => {
    updateAvatarEditorSize();
    hasSelectedNewAvatar.value = false;
    avatarUploadLoading.value = false;
    avatarUploadKey.value += 1;
    showAvatarModal.value = true;
};

const closeAvatarModal = () => {
    if (avatarUploadLoading.value) return;
    showAvatarModal.value = false;
    hasSelectedNewAvatar.value = false;
};

const markAvatarSelected = () => {
    hasSelectedNewAvatar.value = true;
};

const startAvatarUpload = () => {
    if (hasSelectedNewAvatar.value) {
        avatarUploadLoading.value = true;
    }
};

const beforeAvatarUpload = () => {
    if (!hasSelectedNewAvatar.value) {
        avatarUploadLoading.value = false;
        ElMessage.info("Choose a new image before confirming.");
        return false;
    }

    return true;
};

const handleUploadSuccess = (res) => {
    avatarUploadLoading.value = false;
    let response;

    try {
        response = JSON.parse(res);
    } catch {
        ElMessage({
            type: "error",
            message: "Failed to update profile image. Please try again.",
        });
        return;
    }

    if (!response?.success) {
        ElMessage.error(response?.message || "Failed to update profile image.");
        return;
    }

    authStore.updateAvatar(response.url);
    authStore.user.has_custom_avatar = true;
    ElMessage({
        type: "success",
        message: response.message || "Profile updated successfully.",
    });
};

const handleUploadError = (error) => {
    avatarUploadLoading.value = false;
    ElMessage.error(
        error?.message || "Failed to update profile image. Please try again.",
    );
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
                        :key="avatarUploadKey"
                        :headers="uploadHeaders"
                        lang="en"
                        v-show="showAvatarModal"
                        :url="uploadURL"
                        field="avatar"
                        :width="avatarEditorSize"
                        :height="avatarEditorSize"
                        :select-size="avatarEditorSize"
                        :showPreview="false"
                        :avatar="authStore.user?.avatar"
                        :on-befoure-upload="beforeAvatarUpload"
                        :class="{ 'avatar-uploading': avatarUploadLoading }"
                        @input="markAvatarSelected"
                        @close="closeAvatarModal"
                        @success="handleUploadSuccess"
                        @error="handleUploadError"
                    >
                        <template #cancel>
                            <el-button class="avatar-upload-cancel">Cancel</el-button>
                        </template>
                        <template #confirm>
                            <el-button
                                type="primary"
                                class="avatar-upload-confirm"
                                :disabled="!hasSelectedNewAvatar || avatarUploadLoading"
                                :loading="avatarUploadLoading"
                                :aria-busy="avatarUploadLoading"
                                :aria-label="hasSelectedNewAvatar ? 'Confirm avatar upload' : 'Select a new image to enable upload'"
                                @click="startAvatarUpload"
                            >
                                {{ avatarUploadLoading ? "Uploading..." : hasSelectedNewAvatar ? "Confirm" : "Select image first" }}
                            </el-button>
                        </template>
                    </VueAvatarUpload>
                </el-col>
                <el-col :xs="24" :sm="14">
                    <div class="flex flex-wrap justify-center lg:justify-end gap-2 pl-0 md:pl-4 mt-4 lg:mt-0">
                        <el-button
                            @click="openAvatarModal"
                            type="primary"
                            class="m-0"
                        >
                            <CameraIcon class="w-5 h-5 mr-2" />
                            <span class="text-sm text-inherit">Change avatar</span>
                        </el-button>
                        <el-button
                            class="m-0"
                            plain
                            v-if="authStore.user?.has_custom_avatar"
                            @click="confirmDelete"
                        >
                            <TrashIcon class="w-5 h-5 mr-2" />
                            <span class="text-sm text-inherit">Delete avatar</span>
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <Form :initial-values="{ name: authStore.user.name || '' }" :validation-schema="profileSchema" @submit="saveProfile" class="mt-4">
                <el-row :gutter="20">
                    <el-col :xs="24" :md="12" class="mb-4">
                        <Field name="name" v-slot="{field, handleChange, errorMessage}">
                            <el-form-item label="Name" label-position="top" :error="errorMessage">
                                <el-input type="text" 
                                :model-value="field.value"
                                @update:model-value="handleChange"
                                placeholder="Enter name here"
                                 />
                            </el-form-item>
                        </Field>
                    </el-col>
                    <el-col :xs="24" :md="12" class="">
                            <el-form-item label="Email (cannot be changed)" label-position="top">
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
.avatar-upload-root {
    z-index: 3000;
}

.avatar-upload-root-fixed {
    box-sizing: border-box;
    overflow-y: auto;
    padding: 16px;
}

.avatar-upload-root .avatar-upload {
    box-sizing: border-box;
    max-width: 100%;
    max-height: 100%;
    overflow-y: auto;
}

.avatar-upload-root .avatar-upload-header {
    gap: 12px;
}

.avatar-upload-root .avatar-upload-header .avatar-upload-close {
    display: inline-flex;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    align-items: center;
    justify-content: center;
    border-radius: var(--el-border-radius-base);
    transition: background-color 0.15s ease;
}

.avatar-upload-root .avatar-upload-header .avatar-upload-close:hover {
    background-color: var(--el-fill-color-light);
}

.avatar-upload-root .avatar-upload-main {
    max-width: 100%;
    box-sizing: border-box;
    padding: 10px 0;
}

.avatar-upload-root .avatar-upload-edit {
    max-width: 100%;
}

.avatar-upload-root .avatar-upload-operation {
    display: flex;
    width: 100%;
    height: auto;
    min-height: 32px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    box-sizing: border-box;
    margin: 12px 0 0;
}

.avatar-upload-root .avatar-upload-operation > span:first-child,
.avatar-upload-root .avatar-upload-operation .upload-operation-close {
    display: inline-flex;
    min-height: 32px;
    flex: 0 1 auto;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-sizing: border-box;
    padding: 8px 12px;
    border: 1px solid var(--el-border-color);
    border-radius: var(--el-border-radius-base);
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        color 0.15s ease;
}

.avatar-upload-root .avatar-upload-operation > span:first-child {
    color: var(--el-color-white);
    background-color: var(--el-color-primary);
    border-color: var(--el-color-primary);
}

.avatar-upload-root .avatar-upload-operation > span:first-child:hover {
    background-color: var(--el-color-primary-light-3);
    border-color: var(--el-color-primary-light-3);
}

.avatar-upload-root .avatar-upload-operation .upload-operation-close {
    color: var(--el-text-color-regular);
    background-color: var(--el-fill-color-blank);
}

.avatar-upload-root .avatar-upload-operation .upload-operation-close:hover {
    color: var(--el-color-primary);
    border-color: var(--el-color-primary-light-7);
    background-color: var(--el-color-primary-light-9);
}

.avatar-upload-root .avatar-upload-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin: 16px 0 4px;
}

.avatar-upload-root .avatar-upload-actions > div {
    display: flex;
    flex: 0 1 auto;
    justify-content: center;
}

.avatar-upload-root .avatar-upload-actions .el-button {
    min-width: 100px;
    margin: 0;
}

.avatar-upload-root .avatar-upload-actions > div:last-child .el-button {
    min-width: 150px;
}

.avatar-uploading .avatar-upload-header .avatar-upload-close,
.avatar-uploading .avatar-upload-actions > div:first-child {
    pointer-events: none;
    opacity: 0.5;
}

html.dark .avatar-upload-root {
    --au-text-color: var(--el-text-color-regular);
    --au-btn-color: var(--el-color-primary);
}

html.dark .avatar-upload-root .avatar-upload {
    background-color: var(--el-bg-color-overlay) !important;
    box-shadow: 0 16px 40px rgb(0 0 0 / 40%);
    color: var(--el-text-color-primary);
}

html.dark .avatar-upload-root .avatar-upload-header,
html.dark .avatar-upload-root .avatar-upload-close,
html.dark .avatar-upload-root .avatar-upload-operation {
    color: var(--el-text-color-primary);
}

html.dark .avatar-upload-root .avatar-upload-preview {
    background-color: var(--app-muted);
    color: var(--el-text-color-regular);
}

@media (max-width: 360px) {
    .avatar-upload-root .avatar-upload-operation {
        justify-content: center;
    }

    .avatar-upload-root .avatar-upload-operation > span:first-child,
    .avatar-upload-root .avatar-upload-operation .upload-operation-close {
        flex: 1 1 100%;
    }

    .avatar-upload-root .avatar-upload-actions {
        gap: 8px;
    }

    .avatar-upload-root .avatar-upload-actions > div,
    .avatar-upload-root .avatar-upload-actions .el-button,
    .avatar-upload-root .avatar-upload-actions > div:last-child .el-button {
        width: 100%;
        min-width: 0;
    }
}
</style>
