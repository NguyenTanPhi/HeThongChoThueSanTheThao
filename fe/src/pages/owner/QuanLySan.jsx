// src/pages/owner/QuanLySan.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { useNavigate } from "react-router-dom";

export default function QuanLySan({ setActiveTab }) {
  const [sanList, setSanList] = useState([]);
  const [loading, setLoading] = useState(true); // Trạng thái loading
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [visibleCount, setVisibleCount] = useState(3);


  const [newSan, setNewSan] = useState({
    ten_san: "",
    loai_san: "",
    gia_thue: "",
    dia_chi: "",
    mo_ta: "",
    hinh_anh: null,
  });
  const [diaChi, setDiaChi] = useState({
  so_nha: "",
  phuong_xa: "",
  quan_huyen: "",
  thanh_pho: "",
});

  const [toast, setToast] = useState(null);
  const [deleteSanId, setDeleteSanId] = useState(null);

  const navigate = useNavigate();

  const checkPackageBeforeAdd = async () => {
    try {
      const res = await axiosPrivate.get("/owner/goi-hien-tai");
      const goi = res.data;

      if (!goi || goi.trang_thai !== "con_han") {
        setToast({
          type: "error",
          message: "Gói dịch vụ đã hết hạn hoặc chưa có. Vui lòng mua gói dịch vụ!",
        });
        setTimeout(() => setActiveTab("goi-dich-vu"), 2000);
        return;
      }

      setIsAddModalOpen(true);
    } catch (err) {
      console.error(err);
      setToast({ type: "error", message: "Không kiểm tra được gói dịch vụ!" });
    } finally {
      setTimeout(() => setToast(null), 3000);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await axiosPrivate.get("/owner/my-san");
      setSanList(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const goToDetail = (id) => navigate(`/owner/san/${id}`);

  const handleDeleteSan = async (id) => {
    try {
      const res = await axiosPrivate.delete(`/san/${id}`);
      if (res.data.success) {
        setSanList((prev) => prev.filter((san) => san.id !== id));
        setToast({ type: "success", message: "Đã xóa sân thành công!" });
      } else {
        setToast({ type: "error", message: res.data.message || "Không thể xóa sân!" });
      }
    } catch (err) {
      console.error(err);
      const errmess = err.response?.data?.message || "Có lỗi khi xóa sân!";
      setToast({ type: "error", message: errmess });
    } finally {
      setTimeout(() => setToast(null), 3000);
    }
  };

  const handleAddSan = async () => {
  if (isSubmitting) return;
  setIsSubmitting(true);

  // ✅ GỘP ĐỊA CHỈ
  const diaChiDayDu = [
    diaChi.so_nha,
    diaChi.phuong_xa,
    diaChi.quan_huyen,
    diaChi.thanh_pho,
  ]
    .map((s) => s.trim())
    .filter(Boolean)
    .join(", ");

  // ✅ CHECK FRONTEND TRƯỚC
  if (diaChiDayDu.split(",").length < 4) {
    setToast({
      type: "error",
      message: "Vui lòng nhập đầy đủ địa chỉ",
    });
    setIsSubmitting(false);
    return;
  }

  const formData = new FormData();

  // ❌ KHÔNG GỬI newSan.dia_chi
  Object.keys(newSan).forEach((key) => {
    if (key !== "dia_chi") {
      formData.append(key, newSan[key]);
    }
  });

  // ✅ GỬI ĐỊA CHỈ ĐÃ GỘP
  formData.append("dia_chi", diaChiDayDu);

  try {
    const res = await axiosPrivate.post("/owner/san", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (res.data.success) {
      setToast({ type: "success", message: res.data.message });
      setIsAddModalOpen(false);

      setNewSan({
        ten_san: "",
        loai_san: "",
        gia_thue: "",
        dia_chi: "",
        mo_ta: "",
        hinh_anh: null,
      });

      setDiaChi({
        so_nha: "",
        phuong_xa: "",
        quan_huyen: "",
        thanh_pho: "",
      });

      fetchData();
    } else {
      setToast({ type: "error", message: res.data.message });
    }
  } catch (err) {
    setToast({
      type: "error",
      message: err.response?.data?.message || "Có lỗi khi đăng ký sân!",
    });
  } finally {
    setIsSubmitting(false);
    setTimeout(() => setToast(null), 3000);
  }
};


  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-5xl mx-auto">
        <h1 className="text-4xl font-bold text-center mb-4 text-gray-800">Quản lý sân</h1>
        <p className="text-center text-gray-600 mb-6">
          Xem, thêm hoặc xóa sân. Quản lý mọi thông tin liên quan đến sân của bạn.
        </p>

        <div className="flex justify-center mb-6">
          <button className="btn btn-success" onClick={checkPackageBeforeAdd}>
            + Đăng ký sân mới
          </button>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-20">
            <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
            <p className="text-lg text-gray-600 font-medium">Đang tải danh sách sân...</p>
          </div>
        ) : sanList.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-6xl mb-4">🏟️</div>
            <p className="text-xl text-gray-500">
              Chưa có sân nào. Khi thêm sân, danh sách sẽ hiện ở đây.
            </p>
          </div>
        ) : (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-1">
            {sanList.slice(0, visibleCount).map((san) => (
              <div
                key={san.id}
                className="bg-white rounded-2xl shadow-lg border border-gray-100 hover:shadow-2xl transition-all cursor-pointer overflow-hidden"
                onClick={() => goToDetail(san.id)}
              >
                <div className="relative">
                  {san.hinh_anh && (
                    <img
                      src={`${san.hinh_anh}`}
                      alt={san.ten_san}
                      className="w-full h-48 object-cover rounded-t-2xl"
                    />
                  )}
                  <span
                    className={`absolute top-3 right-3 px-3 py-1 rounded-full text-sm font-bold ${
                      san.trang_thai_duyet === "da_duyet"
                        ? "bg-green-100 text-green-800"
                        : san.trang_thai_duyet === "cho_duyet"
                        ? "bg-yellow-100 text-yellow-800"
                        : "bg-gray-100 text-gray-800"
                    }`}
                  >
                    {san.trang_thai_duyet === "da_duyet"
                      ? "Đang hoạt động"
                      : san.trang_thai_duyet === "cho_duyet"
                      ? "Chờ duyệt"
                      : san.trang_thai_duyet === "tu_choi"
                      ? "Bị từ chối"
                      : san.trang_thai_duyet}
                  </span>
                </div>

                <div className="p-6 space-y-2">
                  <p>
                    <b>Tên sân:</b> {san.ten_san}
                  </p>
                  <p>
                    <b>Loại sân:</b> {san.loai_san}
                  </p>
                  <p>
                    <b>Địa chỉ:</b> {san.dia_chi || "Chưa có"}
                  </p>
                  <p>
                    <b>Giá:</b>{" "}
                    {san.gia_thue
                      ? Number(san.gia_thue).toLocaleString("vi-VN") + "đ"
                      : "Chưa có giá"}
                  </p>
                </div>

                <div className="flex justify-end p-4 border-t border-gray-100">
                  <button
                    className="btn btn-error btn-sm"
                    onClick={(e) => {
                      e.stopPropagation();
                      setDeleteSanId(san.id);
                    }}
                  >
                    Xóa
                  </button>
                </div>

                {/* Modal xác nhận xóa */}
                {deleteSanId === san.id && (
                  <div
                    className="fixed inset-0 flex items-center justify-center z-50"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <div
                      className="absolute inset-0 bg-black bg-opacity-50"
                      onClick={() => setDeleteSanId(null)}
                    ></div>
                    <div className="bg-white rounded-xl shadow-lg p-6 z-10 w-full max-w-sm mx-4">
                      <h2 className="text-xl font-bold mb-4">Xác nhận xóa sân</h2>
                      <p className="mb-4">
                        Bạn có chắc muốn xóa sân <b>"{san.ten_san}"</b> không?
                      </p>
                      <div className="flex justify-end gap-3">
                        <button className="btn" onClick={() => setDeleteSanId(null)}>
                          Hủy
                        </button>
                        <button
                          className="btn btn-error" 
                          onClick={() => {
                            handleDeleteSan(san.id);
                            setDeleteSanId(null);
                          }}
                        >
                          Xóa
                        </button>
                      </div>
                    </div>
                  </div>
                )}
                
              </div>
              
            ))}
          </div>
        )}
        {/* Xem thêm / Thu gọn */}
{sanList.length > 3 && (
  <div className="flex justify-center mt-8 gap-4">
    {visibleCount < sanList.length ? (
      <button
        className="btn btn-outline btn-success"
        onClick={() => setVisibleCount((prev) => prev + 3)}
      >
        👀 Xem thêm
      </button>
    ) : (
      <button
        className="btn btn-outline btn-secondary"
        onClick={() => setVisibleCount(3)}
      >
        🔼 Thu gọn
      </button>
    )}
  </div>
)}



        {/* Modal đăng ký sân mới */}
        {isAddModalOpen && (
          <div className="fixed inset-0 flex items-center justify-center z-50">
            <div
              className="absolute inset-0 bg-black bg-opacity-50"
              onClick={() => setIsAddModalOpen(false)}
            ></div>
            <div className="bg-white rounded-xl shadow-lg p-6 z-10 w-full max-w-md mx-4">
              <h2 className="text-2xl font-bold mb-4">Đăng ký sân mới</h2>
              <div className="space-y-3">
                <input
                  type="text"
                  placeholder="Tên sân"
                  className="input input-bordered w-full"
                  value={newSan.ten_san}
                  onChange={(e) => setNewSan({ ...newSan, ten_san: e.target.value })}
                />
                <select
                  className="select select-bordered w-full"
                  value={newSan.loai_san}
                  onChange={(e) => setNewSan({ ...newSan, loai_san: e.target.value })}
                >
                  <option value="">-- Chọn loại sân --</option>
                  <option>Sân 5 người</option>
                  <option>Sân 7 người</option>
                  <option>Sân 11 người</option>
                  <option>Sân Cầu lông</option>
                  <option>Sân Pickleball</option>
                  <option>Sân Tenis</option>
                  <option>Sân Bóng rổ</option>
<option>Sân Bóng chuyền</option>
<option>Sân Futsal</option>
                </select>
                <input
                  type="number"
                  placeholder="Giá thuê"
                  className="input input-bordered w-full"
                  value={newSan.gia_thue}
                  onChange={(e) => setNewSan({ ...newSan, gia_thue: e.target.value })}
                />
                <div className="grid grid-cols-1 gap-3">
  <input
    type="text"
    placeholder="Số nhà, tên đường"
    className="input input-bordered w-full"
    value={diaChi.so_nha}
    onChange={(e) => setDiaChi({ ...diaChi, so_nha: e.target.value })}
  />

  <input
    type="text"
    placeholder="Phường / Xã"
    className="input input-bordered w-full"
    value={diaChi.phuong_xa}
    onChange={(e) => setDiaChi({ ...diaChi, phuong_xa: e.target.value })}
  />

  <input
    type="text"
    placeholder="Quận / Huyện"
    className="input input-bordered w-full"
    value={diaChi.quan_huyen}
    onChange={(e) => setDiaChi({ ...diaChi, quan_huyen: e.target.value })}
  />

  <input
    type="text"
    placeholder="Tỉnh / Thành phố"
    className="input input-bordered w-full"
    value={diaChi.thanh_pho}
    onChange={(e) => setDiaChi({ ...diaChi, thanh_pho: e.target.value })}
  />
</div>

                <textarea
                  placeholder="Mô tả"
                  className="textarea textarea-bordered w-full"
                  value={newSan.mo_ta}
                  onChange={(e) => setNewSan({ ...newSan, mo_ta: e.target.value })}
                />
                <input
                  type="file"
                  className="file-input file-input-bordered w-full"
                  onChange={(e) => setNewSan({ ...newSan, hinh_anh: e.target.files[0] })}
                />
              </div>
              <div className="flex justify-end gap-3 mt-6">
                <button className="btn" onClick={() => setIsAddModalOpen(false)}>
                  Hủy
                </button>
                <button
                  className="btn btn-success"
                  onClick={handleAddSan}
                  disabled={isSubmitting}
                >
                  {isSubmitting ? (
                    <>
                      <span className="loading loading-spinner loading-sm"></span>
                      Đang đăng ký...
                    </>
                  ) : (
                    "Đăng ký"
                  )}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Toast thông báo */}
        {toast && (
          <div
            className={`fixed bottom-5 right-5 flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg transition-all text-white z-50
              ${toast.type === "success" ? "bg-green-600" : "bg-red-600"}`}
          >
            <span className="font-semibold">{toast.message}</span>
            {toast.message.includes("gói dịch vụ") && (
              <button
                className="ml-3 bg-white text-red-600 px-3 py-1 rounded hover:bg-gray-200 text-sm font-medium"
                onClick={() => {
                  setToast(null);
                  setActiveTab("goi-dich-vu");
                }}
              >
                Mua gói ngay
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
}