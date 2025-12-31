// src/pages/owner/QuanLySan.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { useNavigate } from "react-router-dom";

export default function QuanLySan({ setActiveTab }) {
  const [sanList, setSanList] = useState([]);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [newSan, setNewSan] = useState({
    ten_san: "",
    loai_san: "",
    gia_thue: "",
    dia_chi: "",
    mo_ta: "",
    hinh_anh: null,
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
      const res = await axiosPrivate.get("/owner/my-san");
      setSanList(res.data || []);
    } catch (err) {
      console.error(err);
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
      const errmess = err.response.data.message;
      setToast({ type: "error", message: errmess });
    } finally {
      setTimeout(() => setToast(null), 3000);
    }
  };

  const handleAddSan = async () => {
    if (isSubmitting) return;

  setIsSubmitting(true);
    const formData = new FormData();
    Object.keys(newSan).forEach((key) => formData.append(key, newSan[key]));

    try {
      const res = await axiosPrivate.post("/owner/san", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      if (res.data.success) {
        setToast({ type: "success", message: res.data.message || "Đăng ký sân thành công!" });
        setIsAddModalOpen(false);
        setNewSan({
          ten_san: "",
          loai_san: "",
          gia_thue: "",
          dia_chi: "",
          mo_ta: "",
          hinh_anh: null,
        });
        fetchData();
      } else if (res.data.require_package) {
        setToast({ type: "error", message: res.data.package_message });
      } else {
        setToast({ type: "error", message: "Không thể đăng ký sân mới!" });
      }
    } catch (err) {
      console.error(err);
      setToast({ type: "error", message: "Có lỗi khi đăng ký sân!" });
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

        {sanList.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-6xl mb-4">🏟️</div>
            <p className="text-xl text-gray-500">Chưa có sân nào. Khi thêm sân, danh sách sẽ hiện ở đây.</p>
          </div>
        ) : (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-1">
            {sanList.map((san) => (
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
                    {san.gia_thue ? Number(san.gia_thue).toLocaleString("vi-VN") + "đ" : "Chưa có giá"}
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

                {/* Modal xóa */}
                {deleteSanId === san.id && (
                  <div
                    className="fixed inset-0 flex items-center justify-center z-50"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <div
                      className="absolute inset-0 bg-black bg-opacity-50"
                      onClick={() => setDeleteSanId(null)}
                    ></div>
                    <div className="bg-white rounded-xl shadow-lg p-6 z-10 w-full max-w-sm">
                      <h2 className="text-xl font-bold mb-4">Xác nhận xoá sân</h2>
                      <p className="mb-4">
                        Bạn có chắc muốn xoá sân <b>"{san.ten_san}"</b> không?
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

        {/* Modal đăng ký sân */}
        {isAddModalOpen && (
          <div className="fixed inset-0 flex items-center justify-center z-50">
            <div className="absolute inset-0 bg-black bg-opacity-50" onClick={() => setIsAddModalOpen(false)}></div>
            <div className="bg-white rounded-xl shadow-lg p-6 z-10 w-full max-w-md">
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
                </select>
                <input
                  type="number"
                  placeholder="Giá thuê"
                  className="input input-bordered w-full"
                  value={newSan.gia_thue}
                  onChange={(e) => setNewSan({ ...newSan, gia_thue: e.target.value })}
                />
                <input
                  type="text"
                  placeholder="Địa chỉ"
                  className="input input-bordered w-full"
                  value={newSan.dia_chi}
                  onChange={(e) => setNewSan({ ...newSan, dia_chi: e.target.value })}
                />
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
                <button className="btn btn-success" onClick={handleAddSan} disabled={isSubmitting}>
                 {isSubmitting ? "Đang đăng ký..." : "Đăng ký"}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Toast */}
        {toast && (
          <div
            className={`fixed bottom-5 right-5 flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg transition-all
              ${toast.type === "success" ? "bg-green-600" : "bg-red-600"} text-white`}
          >
            <span className="font-semibold">{toast.message}</span>
            {toast.message.includes("gói dịch vụ") && (
              <button
                className="ml-3 bg-white text-red-600 px-3 py-1 rounded hover:bg-gray-200"
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
