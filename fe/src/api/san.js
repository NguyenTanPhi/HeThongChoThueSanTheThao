// src/api/san.js
import { axiosPublic } from "./instance";

// Lấy danh sách sân
export const getSanList = () => axiosPublic.get("/san");

// Lấy chi tiết 1 sân
export const getSanDetail = (id) => axiosPublic.get(`/san/${id}`);

// Lấy lịch trống của sân theo ngày
export const getLichTrong = (sanId, ngay) => 
  axiosPublic.get(`/san/${sanId}/lich-trong`, { params: { ngay } });