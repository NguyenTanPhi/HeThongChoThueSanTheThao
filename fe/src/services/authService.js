import API from './api';

export const login = (data) => API.post('/login', data);
export const register = (data) => API.post('/register', data);
export const logout = () => API.post('/logout');

export const setUser = (user) => localStorage.setItem('user', JSON.stringify(user));
export const getUser = () => JSON.parse(localStorage.getItem('user'));
export const getRole = () => getUser()?.role; // admin, owner, customer
