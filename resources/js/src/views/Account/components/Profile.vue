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
import "element-plus/es/components/";
import "element-plus/es/components/loading/style/css";
import { deleteAvatar } from "../../../services/user";

const authStore = useAuthStore();
const showAvatarModal = ref(false);
const uploadURL = `${import.meta.env.VITE_API_URL}/upload-avatar`;

const uploadHeaders = {
    "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
};

const handleUploadSuccess = (res) => {
    const response = JSON.parse(res);
    if (response && response.success) {
        authStore.updateAvatar(response.url);
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
    try{
        const response = await deleteAvatar();
        if(response.data.success){
            ElMessage({
                type: "success",
                message: res.data.message || "Profile image deleted successfully"
            })
        }

        throw new Error(res);
    }
    catch(e){
        handleError(e);
    }
}

const open = () => {
  ElMessageBox.confirm(
    'proxy will permanently delete the file. Continue?',
    'Warning',
    {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(() => {
      ElMessage({
        type: 'success',
        message: 'Delete completed',
      })
    })
    .catch(() => {
      ElMessage({
        type: 'info',
        message: 'Delete canceled',
      })
    })
}

</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Profile</h4>
        <div>
            <el-row class="flex items-center">
                <el-col :xs="24" :md="6">
                    <Avatar :size="150" :avatarURL="authStore.user?.avatar" />

                    <VueAvatarUpload
                        :headers="uploadHeaders"
                        lang="en"
                        v-show="showAvatarModal"
                        :url="uploadURL"
                        field="avatar"
                        :avatar="authStore.user?.avatar"
                        @close="showAvatarModal = false"
                        @success="handleUploadSuccess"
                        @error="handleUploadError"
                    />
                </el-col>
                <el-col :xs="24" :md="18">
                    <div>
                        <el-button @click="showAvatarModal = true" type="primary">
                            <CameraIcon class="w-5 h-5 mr-2" />
                            <span class="text-sm">Upload new</span>
                        </el-button>
                        <el-button plain v-if="authStore.user?.has_custom_avatar" @click="deleteProfileImage">
                            <TrashIcon class="w-5 h-5 mr-2" />
                            <span class="text-sm">Delete Avatar</span>
                        </el-button>
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<style>
.avatar-upload-actions .avatar-button.-salmon {
    background-color: var(--el-color-primary) !important;
}
</style>
