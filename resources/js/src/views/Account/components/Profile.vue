<script setup>
import VueAvatarUpload from '@pkhadson/vue-avatar-upload';
import { useAvatarUpload } from "@pkhadson/vue-avatar-upload";
import "@pkhadson/vue-avatar-upload/lib/style.css";
import Avatar from '../../../components/ui/Avatar.vue';

const avatarUpload = useAvatarUpload({
    color: "#ff5478",
    default: {
        // pass default props
    },
});

const openUpload = () => {
    avatarUpload({
        onCustomRequest: (file, close) => {
            // upload flow
            close(); // close pop-up
        },
    });
};
</script>

<template>
    <div class="p-6">
        <h4 class="text-xl font-medium mb-4">Profile</h4>
        <div>
            <el-row class="flex items-center">
                <el-col :xs="24" :md="6">
                    <Avatar :size="150" />
                </el-col>
                <el-col :xs="24" :md="18">
                    <div>
                        <el-button>Upload New</el-button>
                        <el-button>Delete Avatar</el-button>
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
</template>
