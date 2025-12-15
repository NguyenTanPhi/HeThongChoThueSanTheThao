import API from './api';

// Khách hàng
export const getAllSans = () => API.get('/san');
export const getSan = (id) => API.get(`/san/${id}`);
export const getLichTrong = (id, ngay) => API.get(`/lich-san/${id}/${ngay}`);
export const datSan = (data) => API.post('/dat-san', data);

// Chủ sân
export const getMySans = () => API.get('/owner/my-san');
export const themSan = (data) => API.post('/owner/san', data);
export const suaSan = (id, data) => API.put(`/san/${id}`, data);
export const xoaSan = (id) => API.delete(`/san/${id}`);
export const getLichTrongOwner = (id) => API.get(`/owner/san/${id}/lich-trong`);
