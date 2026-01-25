import { ElMessage } from "element-plus";

const handleError = (error) => {
    let message = 'Something went wrong! Please try again.';

    if (error.response) {
        const { status, data } = error.response;

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
    } 
    else if (error.request) {
        message = 'Server did nt respond! Please try again later.';
    } 
    else {
        message = error.message || message;
    }

    ElMessage({
        message,
        type: 'error',
        duration: 5000
    });
};

export default handleError;
