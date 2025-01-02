import axios from 'axios';

// Add CSRF token to all requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Add base URL if needed
// axios.defaults.baseURL = '/api/v1';

// Add request interceptor
axios.interceptors.request.use(
    config => {
        // You can add any request modifications here
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// Add response interceptor
axios.interceptors.response.use(
    response => {
        return response;
    },
    error => {
        if (error.response) {
            // Handle specific error codes
            switch (error.response.status) {
                case 401:
                    // Handle unauthorized
                    console.error('Unauthorized request');
                    break;
                case 403:
                    // Handle forbidden
                    console.error('Forbidden request');
                    break;
                case 404:
                    // Handle not found
                    console.error('Resource not found');
                    break;
                case 422:
                    // Handle validation errors
                    console.error('Validation error', error.response.data.errors);
                    break;
                case 500:
                    // Handle server error
                    console.error('Server error');
                    break;
                default:
                    console.error('Request failed');
            }
        }
        return Promise.reject(error);
    }
);

export default axios;
