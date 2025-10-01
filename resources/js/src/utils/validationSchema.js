import * as yup from 'yup';

export const registerSchema = yup.object({
    name: yup.string().required('Name is required').min(3, 'Name should be atleast of 3 letters'),
    email: yup.string().required('Email is required').email('Your email is invalid'),
    password: yup.string().required('Password is required').matches(/^(?=.*[A-Z])(?=.*\d).{8,}$/, 'Password must contain 1 uppercase letter and 1 digit'),
    password_confirmation: yup.string().required('Confirm password is required').oneOf([yup.ref('password')], 'Passwords do not match')
});

export const loginSchema = yup.object({
    email: yup.string().required('Email is required').email('Your email is invalid'),
    password: yup.string().required('Password is required').matches(/^(?=.*[A-Z])(?=.*\d).{8,}$/, 'Password must contain 1 uppercase letter and 1 digit'),
    remember_me: yup.boolean()
});