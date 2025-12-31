import { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function SanChoDuyet() {
  const [sanList, setSanList] = useState([]);
  const [detailSan, setDetailSan] = useState(null);
  const [rejectId, setRejectId] = useState(null);
  const [reason, setReason] = useState("");

  useEffect(() => {
    axiosPrivate
      .get("/admin/san/cho-duyet")
      .then((res) => setSanList(res.data))
      .catch((err) => console.error(err));
  }, []);

  const duyetSan = async (id) => {
    try {
      await axiosPrivate.post(`/admin/san/${id}/duyet`, {
        trang_thai_duyet: "da_duyet",
      });

      setSanList((prev) => prev.filter((s) => s.id !== id));
      showToast("✅ Duyệt sân thành công!", "success");
    } catch (err) {
      showToast("❌ Lỗi duyệt sân!", "error");
    }
  };

  const tuChoiSan = async () => {
    if (!reason.trim()) {
      showToast("⚠️ Vui lòng nhập lý do từ chối!", "warning");
      return;
    }

    try {
      await axiosPrivate.post(`/admin/san/${rejectId}/duyet`, {
        trang_thai_duyet: "tu_choi",
        ly_do: reason,
      });

      setSanList((prev) => prev.filter((s) => s.id !== rejectId));
      setRejectId(null);
      setReason("");

      showToast("✅ Từ chối sân thành công!", "success");
    } catch (err) {
      showToast("❌ Lỗi từ chối sân!", "error");
    }
  };

  return (
    <div className="p-6">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-8 text-gray-800 tracking-tight">
        🏟️ Sân chờ duyệt
      </h1>

      {sanList.length === 0 ? (
        <div className="p-8 bg-white rounded-2xl shadow text-center text-gray-600 border border-gray-100">
          Không có sân nào chờ duyệt
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {sanList.map((san) => (
            <div
              key={san.id}
              className="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all hover:shadow-2xl hover:-translate-y-1"
            >
              <div className="flex gap-5">
                <img
                  src={
                    san.hinh_anh
                      ? `${san.hinh_anh}`
                      : "/no-image.png"
                  }
                  className="w-32 h-32 object-cover rounded-xl shadow-md"
                />

                <div className="flex-1">
                  <p className="font-bold text-xl text-gray-900">{san.ten_san}</p>
                  <p className="text-gray-600 mt-1">{san.dia_chi}</p>
                  <p className="text-sm text-gray-500 mt-2">
                    👤 Chủ sân: <span className="font-medium">{san.owner?.name}</span>
                  </p>
                </div>
              </div>

              <div className="flex justify-end gap-4 mt-6">
                <button
                  className="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium"
                  onClick={() => setDetailSan(san)}
                >
                  👁 Xem
                </button>

                <button
                  className="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700 transition font-medium"
                  onClick={() => duyetSan(san.id)}
                >
                  ✅ Duyệt
                </button>

                <button
                  className="px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition font-medium"
                  onClick={() => setRejectId(san.id)}
                >
                  ❌ Từ chối
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ✅ Modal chi tiết sân */}
      {detailSan && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
          <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-xl animate-fadeIn">
            <h3 className="font-bold text-2xl mb-4 text-gray-900">Chi tiết sân</h3>

            <img
              src={
                detailSan.hinh_anh
                  ? `http://localhost:8000/storage/${detailSan.hinh_anh}`
                  : "/no-image.png"
              }
              className="w-full h-64 object-cover rounded-xl shadow mb-5"
            />

            <div className="space-y-2 text-gray-700">
              <p><strong>Tên sân:</strong> {detailSan.ten_san}</p>
              <p><strong>Loại sân:</strong> {detailSan.loai_san}</p>
              <p><strong>Giá thuê:</strong> {detailSan.gia_thue?.toLocaleString()}đ</p>
              <p><strong>Địa chỉ:</strong> {detailSan.dia_chi}</p>
              <p><strong>Mô tả:</strong> {detailSan.mo_ta || "Không có mô tả"}</p>

              <p className="mt-3 font-semibold">Chủ sân:</p>
              <p>{detailSan.owner?.name} - {detailSan.owner?.phone}</p>
            </div>

            <div className="flex justify-end mt-6">
              <button
                className="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
                onClick={() => setDetailSan(null)}
              >
                Đóng
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ✅ Modal từ chối */}
      {rejectId && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
          <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md animate-fadeIn">
            <h3 className="font-bold text-xl mb-3 text-gray-900">Nhập lý do từ chối</h3>

            <textarea
              className="textarea textarea-bordered w-full rounded-xl border-gray-300 focus:ring focus:ring-red-200"
              rows="4"
              placeholder="Nhập lý do..."
              value={reason}
              onChange={(e) => setReason(e.target.value)}
            ></textarea>

            <div className="flex justify-end gap-4 mt-5">
              <button
                className="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
                onClick={() => setRejectId(null)}
              >
                Hủy
              </button>

              <button
                className="px-5 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition"
                onClick={tuChoiSan}
              >
                Xác nhận từ chối
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/* ✅ Toast đẹp */
function ToastContainer() {
  if (!window.toast) {
    window.toast = {
      show: (msg, type = "info") => {
        const div = document.createElement("div");
        div.className = `alert alert-${type} shadow-lg mb-3 bg-white border-l-4 rounded-xl px-4 py-3 ${
          type === "success"
            ? "border-green-500"
            : type === "error"
            ? "border-red-500"
            : type === "warning"
            ? "border-yellow-500"
            : "border-blue-500"
        }`;
        div.innerHTML = `<span class="font-medium">${msg}</span>`;

        document.getElementById("toast-root").appendChild(div);

        setTimeout(() => div.remove(), 2500);
      },
    };
  }

  return <div id="toast-root" className="fixed top-4 right-4 z-50"></div>;
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
