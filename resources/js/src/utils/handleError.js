import { ElMessage } from "element-plus";

const handleError = (error) => {
    if (!error.response) {
        ElMessage({
            message: 'Network error! Please check your connection.',
            type: 'error',
        });
        return;
    }

    const { status, data } = error.response;

    let message = 'Something went wrong! Please try again.';

    if (status === 422 && data?.errors) {
        const fieldErrors = Object.values(data.errors)
            .flat()
            .join(', ');
        message = `${fieldErrors}`;
    } 
    else if (data?.message) {
        message = data.message;
    } 
    else if (typeof data === 'string') {
        message = data;
    }

    ElMessage({
        message,
        type: 'error',
    });
}

export default handleError;
