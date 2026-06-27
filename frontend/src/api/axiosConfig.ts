import axios from 'axios';

// Dev: VITE_API_URL is unset, so we use '/api' and Vite proxies it to
// http://localhost:8000 (see vite.config.ts).
// Prod (GitHub Pages): VITE_API_URL points at the Fly.io API, e.g.
//   https://mbg-trace-api.fly.dev/api
const baseURL = import.meta.env.VITE_API_URL ?? '/api';

const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;
