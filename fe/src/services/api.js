import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL;
const API = axios.create({
  baseURL: API_URL, 
});

API.interceptors.request.use((config) => {
  const token = localStorage.getItem('token'); // lưu token sau login
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export default API;
