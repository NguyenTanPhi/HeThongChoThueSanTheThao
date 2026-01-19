import React, { useEffect, useState } from "react";
import { getSanList } from "../../api/san.js";
import SanCard from "../../components/SanCard.jsx";
import Header from "../../components/Header";
import { useNavigate } from "react-router-dom";
function normalizeAddress(text = "") {
  return text
    .toLowerCase()
    .normalize("NFD") // bỏ dấu
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/tp\.?|thanh pho/g, "")
    .replace(/quan|q\.?|huyen|h\.?/g, "")
    .replace(/,/g, "")
    .trim();
}
// Thêm normalizeText cho tên
function normalizeText(text = "") {
  return text
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}

export default function Home() {
  const [sanList, setSanList] = useState([]);
  const [loading, setLoading] = useState(true);

  // ✅ FILTER STATE
  const [filters, setFilters] = useState({
    ten_san: "",
    dia_chi: "",
    minPrice: "",
    maxPrice: "",
  });

  // ✅ PAGINATION STATE
  const [currentPage, setCurrentPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const itemsPerPage = 12; // Theo API trả về

  const navigate = useNavigate();

  // ✅ LOAD DATA TỪ API (phân trang server)
  useEffect(() => {
    setLoading(true);
    const params = {
      page: currentPage,
      per_page: itemsPerPage,
    };
    if (filters.ten_san.trim()) {
      const v = filters.ten_san.trim();
      params.ten_san = v;
      params.keyword = v; // gửi kèm alias nếu backend dùng 'keyword'
    }
    if (filters.dia_chi.trim()) params.dia_chi = filters.dia_chi.trim();
    if (filters.minPrice !== "" && !isNaN(Number(filters.minPrice)))
      params.min_price = filters.minPrice;
    if (filters.maxPrice !== "" && !isNaN(Number(filters.maxPrice)))
      params.max_price = filters.maxPrice;

    getSanList(params)
      .then((res) => {
        const data = res.data;
        let list = data.data || [];

        // Fallback lọc tên ở client nếu backend không lọc
        if (filters.ten_san.trim()) {
          const q = normalizeText(filters.ten_san);
          const filtered = list.filter((s) =>
            normalizeText(s.ten_san || "").includes(q),
          );
          const totalLocal = filtered.length;
          const lastPageLocal = Math.max(
            1,
            Math.ceil(totalLocal / itemsPerPage),
          );
          const start = (currentPage - 1) * itemsPerPage;
          const pageList = filtered.slice(start, start + itemsPerPage);

          setSanList(pageList);
          setTotal(totalLocal);
          setLastPage(lastPageLocal);
        } else {
          // Giữ phân trang server khi không lọc tên
          setSanList(list);
          setTotal(data.total || 0);
          setLastPage(data.last_page || 1);
        }

        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [currentPage, filters]);

  // ✅ RESET PAGE KHI FILTER THAY ĐỔI
  // Đảm bảo chỉ reset khi filter thực sự thay đổi
  const prevFilters = React.useRef(filters);
  useEffect(() => {
    if (prevFilters.current !== filters) {
      setCurrentPage(1);
      prevFilters.current = filters;
    }
  }, [filters]);

  return (
    <>
      <Header />
      {/* HERO */}
      <div className="relative h-[480px] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-green-600 to-emerald-800 opacity-95"></div>
        <div className="absolute inset-0 bg-[url('https://heropatterns.com/patterns/microbial-mat.png')] opacity-15"></div>

        <div className="relative z-10 text-center text-white px-4">
          <h1 className="text-6xl font-bold mb-6">Đặt Sân Bóng Nhanh Chóng</h1>

          <div className="flex gap-4 justify-center">
            <a href="#danh-sach-san" className="btn bg-white text-green-700">
              🔍 Tìm sân ngay
            </a>
            <button
              onClick={() => navigate("/lich-su-dat")}
              className="btn btn-warning">
              📖 Lịch sử đặt sân
            </button>
            <button
              onClick={() => navigate("/tai-khoan")}
              className="btn btn-success text-white">
              👤 Tài khoản
            </button>
          </div>
        </div>
      </div>

      {/* DANH SÁCH */}
      <div
        id="danh-sach-san"
        className="container mx-auto px-5 py-16 bg-gray-50">
        <h2 className="text-4xl font-bold text-center mb-10">
          Các sân bóng nổi bật
        </h2>

        {/* FILTER */}
        <div className="bg-white p-6 rounded-3xl shadow-lg mb-10 border border-gray-100">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input
              placeholder="🔍 Tên sân"
              className="input input-bordered"
              value={filters.ten_san}
              onChange={(e) =>
                setFilters((prev) => ({ ...prev, ten_san: e.target.value }))
              }
            />

            <input
              placeholder="📍 Địa chỉ"
              className="input input-bordered"
              value={filters.dia_chi}
              onChange={(e) =>
                setFilters((prev) => ({ ...prev, dia_chi: e.target.value }))
              }
            />

            <input
              type="number"
              placeholder="💰 Giá từ"
              className="input input-bordered"
              value={filters.minPrice}
              onChange={(e) =>
                setFilters((prev) => ({
                  ...prev,
                  minPrice: e.target.value.replace(/^0+/, ""),
                }))
              }
              min={0}
            />

            <input
              type="number"
              placeholder="💸 Giá đến"
              className="input input-bordered"
              value={filters.maxPrice}
              onChange={(e) =>
                setFilters((prev) => ({
                  ...prev,
                  maxPrice: e.target.value.replace(/^0+/, ""),
                }))
              }
              min={0}
            />
          </div>
          {/* QUICK FILTER KHU VỰC */}
          <div className="flex flex-wrap gap-2 mt-4">
            <span className="text-sm text-gray-500 mr-2">📍 Gần bạn:</span>

            {["Quận 7", "Gò Vấp", "Thủ Đức", "Quận 1"].map((area) => (
              <button
                key={area}
                className="btn btn-xs btn-outline"
                onClick={() =>
                  setFilters((prev) => ({
                    ...prev,
                    dia_chi: area,
                  }))
                }>
                {area}
              </button>
            ))}
          </div>

          <div className="flex justify-between items-center mt-6">
            <p className="text-sm text-gray-500">
              🔎 Tìm thấy <b>{total}</b> sân
            </p>

            <button
              className="btn btn-outline btn-sm"
              onClick={() =>
                setFilters({
                  ten_san: "",
                  dia_chi: "",
                  minPrice: "",
                  maxPrice: "",
                })
              }>
              🔄 Xóa bộ lọc
            </button>
          </div>
        </div>

        {/* DANH SÁCH SÂN */}
        {loading ? (
          <div className="flex justify-center py-20">
            <span className="loading loading-spinner loading-lg"></span>
          </div>
        ) : sanList.length === 0 ? (
          <div className="text-center py-20 text-gray-500">
            Không tìm thấy sân phù hợp
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {sanList.map((san) => (
                <SanCard key={san.id} san={san} />
              ))}
            </div>

            {/* PAGINATION */}
            {lastPage > 1 && (
              <div className="flex justify-center gap-2 mt-12">
                <button
                  className="btn btn-sm"
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage((p) => p - 1)}>
                  ⬅ Trước
                </button>

                {Array.from({ length: lastPage }).map((_, i) => (
                  <button
                    key={i}
                    className={`btn btn-sm ${
                      currentPage === i + 1
                        ? "btn-success text-white"
                        : "btn-outline"
                    }`}
                    onClick={() => setCurrentPage(i + 1)}>
                    {i + 1}
                  </button>
                ))}

                <button
                  className="btn btn-sm"
                  disabled={currentPage === lastPage}
                  onClick={() => setCurrentPage((p) => p + 1)}>
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
