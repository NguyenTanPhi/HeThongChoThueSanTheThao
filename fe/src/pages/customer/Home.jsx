import React, { useEffect, useState } from "react";
import { getSanList } from "../../api/san.js";
import SanCard from "../../components/SanCard.jsx";
import Header from "../../components/Header";
import { useNavigate } from "react-router-dom";

export default function Home() {
  const [sanList, setSanList] = useState([]);
  const [loading, setLoading] = useState(true);

  // ✅ FILTER STATE
  const [filters, setFilters] = useState({
    keyword: "",
    dia_chi: "",
    minPrice: "",
    maxPrice: "",
  });

  // ✅ PAGINATION STATE
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 6;

  const navigate = useNavigate();

  useEffect(() => {
    getSanList()
      .then((res) => {
        setSanList(res.data.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  // ✅ FILTER LOGIC
  const filteredSanList = sanList.filter((san) => {
    const matchName = san.ten_san
      .toLowerCase()
      .includes(filters.keyword.toLowerCase());

    const matchAddress = san.dia_chi
      .toLowerCase()
      .includes(filters.dia_chi.toLowerCase());

    const matchMin =
      !filters.minPrice || san.gia_thue >= Number(filters.minPrice);

    const matchMax =
      !filters.maxPrice || san.gia_thue <= Number(filters.maxPrice);

    return matchName && matchAddress && matchMin && matchMax;
  });

  // ✅ RESET PAGE KHI FILTER THAY ĐỔI
  useEffect(() => {
    setCurrentPage(1);
  }, [filters]);

  // ✅ PAGINATION LOGIC
  const totalPages = Math.ceil(filteredSanList.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const currentSanList = filteredSanList.slice(
    startIndex,
    startIndex + itemsPerPage
  );

  return (
    <>
      <Header />

      {/* HERO */}
      <div className="relative h-[480px] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-green-600 to-emerald-800 opacity-95"></div>
        <div className="absolute inset-0 bg-[url('https://heropatterns.com/patterns/microbial-mat.png')] opacity-15"></div>

        <div className="relative z-10 text-center text-white px-4">
          <h1 className="text-6xl font-bold mb-6">
            Đặt Sân Bóng Nhanh Chóng
          </h1>

          <div className="flex gap-4 justify-center">
            <a href="#danh-sach-san" className="btn bg-white text-green-700">
              🔍 Tìm sân ngay
            </a>
            <button
              onClick={() => navigate("/lich-su-dat")}
              className="btn btn-warning"
            >
              📖 Lịch sử đặt sân
            </button>
            <button
              onClick={() => navigate("/tai-khoan")}
              className="btn btn-success text-white"
            >
              👤 Tài khoản
            </button>
          </div>
        </div>
      </div>

      {/* DANH SÁCH */}
      <div
        id="danh-sach-san"
        className="container mx-auto px-5 py-16 bg-gray-50"
      >
        <h2 className="text-4xl font-bold text-center mb-10">
          Các sân bóng nổi bật
        </h2>

        {/* FILTER */}
        <div className="bg-white p-6 rounded-3xl shadow-lg mb-10 border border-gray-100">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input
              placeholder="🔍 Tên sân"
              className="input input-bordered"
              value={filters.keyword}
              onChange={(e) =>
                setFilters({ ...filters, keyword: e.target.value })
              }
            />

            <input
              placeholder="📍 Địa chỉ"
              className="input input-bordered"
              value={filters.dia_chi}
              onChange={(e) =>
                setFilters({ ...filters, dia_chi: e.target.value })
              }
            />

            <input
              type="number"
              placeholder="💰 Giá từ"
              className="input input-bordered"
              value={filters.minPrice}
              onChange={(e) =>
                setFilters({ ...filters, minPrice: e.target.value })
              }
            />

            <input
              type="number"
              placeholder="💸 Giá đến"
              className="input input-bordered"
              value={filters.maxPrice}
              onChange={(e) =>
                setFilters({ ...filters, maxPrice: e.target.value })
              }
            />
          </div>

          <div className="flex justify-between items-center mt-6">
            <p className="text-sm text-gray-500">
              🔎 Tìm thấy <b>{filteredSanList.length}</b> sân
            </p>

            <button
              className="btn btn-outline btn-sm"
              onClick={() =>
                setFilters({
                  keyword: "",
                  dia_chi: "",
                  minPrice: "",
                  maxPrice: "",
                })
              }
            >
              🔄 Xóa bộ lọc
            </button>
          </div>
        </div>

        {/* DANH SÁCH SÂN */}
        {loading ? (
          <div className="flex justify-center py-20">
            <span className="loading loading-spinner loading-lg"></span>
          </div>
        ) : currentSanList.length === 0 ? (
          <div className="text-center py-20 text-gray-500">
            Không tìm thấy sân phù hợp
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {currentSanList.map((san) => (
                <SanCard key={san.id} san={san} />
              ))}
            </div>

            {/* PAGINATION */}
            {totalPages > 1 && (
              <div className="flex justify-center gap-2 mt-12">
                <button
                  className="btn btn-sm"
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage((p) => p - 1)}
                >
                  ⬅ Trước
                </button>

                {Array.from({ length: totalPages }).map((_, i) => (
                  <button
                    key={i}
                    className={`btn btn-sm ${
                      currentPage === i + 1
                        ? "btn-success text-white"
                        : "btn-outline"
                    }`}
                    onClick={() => setCurrentPage(i + 1)}
                  >
                    {i + 1}
                  </button>
                ))}

                <button
                  className="btn btn-sm"
                  disabled={currentPage === totalPages}
                  onClick={() => setCurrentPage((p) => p + 1)}
                >
                  Sau ➡
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </>
  );
}
