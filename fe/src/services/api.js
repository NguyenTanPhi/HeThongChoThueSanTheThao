    import axios from 'axios';

const API = axios.create({
  baseURL: 'http://localhost:8000/api', // backend Laravel
});

API.interceptors.request.use((config) => {
  const token = localStorage.getItem('token'); // lưu token sau login
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export default API;
