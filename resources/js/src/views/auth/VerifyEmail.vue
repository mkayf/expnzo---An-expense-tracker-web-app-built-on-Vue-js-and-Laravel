<script setup>
import { Form, Field } from "vee-validate";
import { emailOTPSchema } from "../../utils/validationSchema";
import { computed, onMounted, ref } from "vue";
import SubmitButton from "../../components/ui/SubmitButton.vue";
import useAuthStore from "../../stores/auth";
import { userToBeVerified } from "../../services/auth";
import handleError from "../../utils/handleError";

const loading = ref(false);
const otpInput = ref(null);
const isFocused = ref(false);
const resendTimer = ref(60);


const authStore = useAuthStore();
const userEmail = computed(() => {
    let email = String(authStore.user.email).split("");
    let newEmail = "";
    for (let i = 0; i < email.length; i++) {
        if (
            i == 0 ||
            i == 1 ||
            i == 2 ||
            i == email.length - 1 ||
            i == email.length - 2 ||
            i == email.length - 3 ||
            i == email.length - 4
        ) {
            newEmail += email[i];
        } else {
            newEmail += "x";
        }
    }
    return newEmail;
});


const verifyEmail = (formData) => {
    console.log(formData);
};

// Resend OTP and timer info:
const canResendOTP = async () => {
    try{
        const res = await userToBeVerified({email: authStore.user.email});
        if(res.data.success){
            resendTimer.value = res.data.timeLeft;
        }
    }
    catch(e){
        handleError(e);
        console.log(e);
    }
};

const startCountDown = () => {
    const countDown = setInterval(() => {
        if(resendTimer.value > 0){
            resendTimer.value -= 1;
        } else{
            clearInterval(countDown);
        }
    }, 1000);
}

const formattedTime = computed(() => {
    return Math.ceil(resendTimer.value);
});


onMounted(() => {
    canResendOTP();
    startCountDown();
    otpInput.value.focus();
    isFocused.value = true;
});

</script>
<template>
    <div class="bg-white p-6 rounded-2xl shadow-lg w-full sm:w-100">
        <Form @submit="verifyEmail" :validation-schema="emailOTPSchema">
            <div class="flex items-center justify-between">
                <h2 class="text-xl">Verify Email</h2>
            </div>
            <span class="text-gray-700 text-xs inline-block mb-3">Enter the OTP which is sent to your email
                {{ userEmail }}</span>
            <div class="otp-container mb-4">
                <Field
                    name="otp"
                    v-slot="{ field, handleChange, errorMessage }"
                >
                    <div class="otp-wrapper">
                        <el-input
                            :model-value="field.value"
                            @input="handleChange"
                            @focus="isFocused = true"
                            @blur="isFocused = false"
                            maxlength="6"
                            class="ghost-input"
                            type="text"
                            ref="otpInput"
                            autocomplete="one-time-code"
                        />

                        <div class="otp-visuals">
                            <div
                                v-for="(box, index) in 6"
                                :key="index"
                                class="otp-box"
                                :class="{
                                    'is-filled':
                                        field.value && field.value[index],
                                    // MODIFIED: Ab isFocused ko bhi check kiya ja raha hai
                                    'is-active':
                                        (field.value
                                            ? field.value.length
                                            : 0) === index && isFocused,
                                }"
                            >
                                {{ field.value ? field.value[index] : "" }}
                            </div>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="text-danger mt-2">
                        {{ errorMessage }}
                    </div>
                </Field>
            </div>          
            <div>
                <span class="text-xs text-gray-700 inline-block mb-2">Didn’t get it? Wait for the timer.</span>
            </div>
            <div class="flex justify-between items-center">
                <el-button :disabled="formattedTime > 0" class="w-full transition-all duration-500"
                    :class="{
                        '!bg-gray-200 hover:!bg-gray-300 hover:!text-[#606266] !border-none': formattedTime > 0,
                        '!bg-[var(--primary-green)] !border-[var(--primary-green)] hover:!bg-[var(--green-hover)] !text-white' : formattedTime == 0
                    }"
                    >
                    <span v-if="formattedTime > 0">{{formattedTime}}s</span>
                    <span v-else>Resend</span>
                </el-button>
                <SubmitButton
                    :is-loading="loading"
                    text="Verify"
                    classes="w-full"
                />
            </div>
        </Form>
    </div>
</template>

<style scoped>

.otp-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.otp-wrapper {
    position: relative;
    width: 100%;
}

.ghost-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    z-index: 10;
    cursor: text;
}

.otp-visuals {
    display: flex;
    gap: 12px;
    width: 100%;
}

.otp-box {
    position: relative;
    width: 100%;
    height: 50px;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
    color: #303133;
    background: white;
    transition: all 0.3s;
}

@keyframes blink {
    0% {
        opacity: 0; 
    }
    50% {
        opacity: 1; 
    }
    100% {
        opacity: 0;
    }
}

.otp-box.is-active::after {
    content: "";
    position: absolute;
    display: block;
    width: 1px;
    height: 60%; 
    background-color: black; 
    top: 20%;
    left: 50%;
    transform: translateX(-50%);
    animation: blink 1s step-end infinite;
}

.text-danger {
    color: #f56c6c;
    font-size: 0.75rem !important;
}
</style>
