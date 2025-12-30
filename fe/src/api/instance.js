import axios from "axios";
const API_URL = import.meta.env.VITE_APP_URL;

const axiosPublic = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
});

const axiosPrivate = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
  //withCredentials: true,
});

// Interceptor tự động đính token
axiosPrivate.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export { axiosPublic, axiosPrivate };