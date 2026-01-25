import * as yup from 'yup';

export const emailSchema = yup.string().required('Email is required').email('Your email is invalid');

export const passwordSchema = yup.string().required('Password is required').matches(/^(?=.*[A-Z])(?=.*\d).{8,}$/, 'Password must contain 1 uppercase letter and 1 digit');

export const confirmPasswordSchema = function (field){
    return yup.string().required('Confirm password is required').oneOf([yup.ref(field)], 'Passwords do not match');
}

export const registerSchema = yup.object({
    name: yup.string().required('Name is required').min(3, 'Name should be atleast 3 letters'),
    email: emailSchema,
    password: passwordSchema,
    password_confirmation: confirmPasswordSchema('password')
});

export const loginSchema = yup.object({
    email: emailSchema,
    password: passwordSchema,
    remember_me: yup.boolean()
});

export const profileSchema = yup.object({
    name: yup.string().required('Name is required').min(3, 'Name should be atleast 3 letters'),
});

export const  emailOTPSchema = yup.object({
    otp: yup
    .string()
    .required('Enter the OTP to verify your email')
    .matches(/^\d{6}$/, 'OTP code must be exactly 6 digits')
})

export const changePasswordSchema = yup.object({
    current_password: passwordSchema,
    new_password: passwordSchema,
    new_password_confirmation: confirmPasswordSchema('new_password')
})