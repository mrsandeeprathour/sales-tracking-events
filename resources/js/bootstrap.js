import axios from 'axios';
import { getSessionToken } from '@shopify/app-bridge-utils';
import createApp from '@shopify/app-bridge';


// Initialize App Bridge
const app = createApp({
    apiKey: import.meta.env.VITE_SHOPIFY_API_KEY,  // Use your actual API key
    shopOrigin: new URLSearchParams(window.location.search).get('shop'),
    host: new URLSearchParams(window.location.search).get('host'),
    forceRedirect: true,
});

window.axios = axios;

// Add X-Requested-With header
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


const setSessionToken  = () => {
    setInterval( async ()=>{
        const token = await getSessionToken(app); // Fetch session token
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },1000)
}

setSessionToken();
