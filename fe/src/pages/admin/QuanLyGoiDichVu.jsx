import { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function GoiDichVu() {
  const [packages, setPackages] = useState([]);
  const [isAddOpen, setIsAddOpen] = useState(false);
  const [isEditOpen, setIsEditOpen] = useState(false);
  const [selected, setSelected] = useState(null);
  const [deleteId, setDeleteId] = useState(null);

  const [form, setForm] = useState({
    ten_goi: "",
    mo_ta: "",
    gia: "",
    thoi_han: "",
    trang_thai: "hoat_dong",
  });

  useEffect(() => {
    fetchPackages();
  }, []);

  const fetchPackages = async () => {
    try {
      const res = await axiosPrivate.get("/admin/goi-dich-vu");
      setPackages(res.data);
    } catch (err) {
      showToast("Không thể tải danh sách gói!", "error");
    }
  };

  const handleAdd = async () => {
    try {
      await axiosPrivate.post("/admin/goi-dich-vu", {
        ...form,
        gia: Number(form.gia),
        thoi_han: Number(form.thoi_han),
      });

      showToast("✅ Thêm gói thành công!", "success");
      setIsAddOpen(false);
      resetForm();
      fetchPackages();
    } catch (err) {
      showToast("❌ Lỗi thêm gói!", "error");
    }
  };

  const handleEdit = async () => {
    try {
      await axiosPrivate.put(`/admin/goi-dich-vu/${selected.id}`, {
        ...form,
        gia: Number(form.gia),
        thoi_han: Number(form.thoi_han),
      });

      showToast("✅ Cập nhật gói thành công!", "success");
      setIsEditOpen(false);
      resetForm();
      fetchPackages();
    } catch (err) {
      showToast("❌ Lỗi cập nhật gói!", "error");
    }
  };

  const handleDelete = async (id) => {
    try {
      await axiosPrivate.delete(`/admin/goi-dich-vu/${id}`);
      showToast("✅ Xóa gói thành công!", "success");
      fetchPackages();
    } catch (err) {
      showToast("❌ Lỗi xóa gói!", "error");
    }
  };

  const resetForm = () => {
    setForm({
      ten_goi: "",
      mo_ta: "",
      gia: "",
      thoi_han: "",
      trang_thai: "hoat_dong",
    });
  };

  return (
    <div className="p-6">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-6 text-gray-800 tracking-tight">
        📦 Quản lý gói dịch vụ
      </h1>

      <button
        className="px-5 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition mb-6 shadow"
        onClick={() => setIsAddOpen(true)}
      >
        + Thêm gói mới
      </button>

      <div className="overflow-x-auto bg-white rounded-2xl shadow-lg border border-gray-100">
        <table className="table table-zebra">
          <thead className="bg-gray-50 text-gray-700 font-semibold">
            <tr>
              <th>Tên gói</th>
              <th>Giá</th>
              <th>Thời hạn</th>
              <th>Trạng thái</th>
              <th className="text-center">Hành động</th>
            </tr>
          </thead>

          <tbody>
            {packages.map((g) => (
              <tr key={g.id} className="hover:bg-gray-50 transition">
                <td className="font-medium">{g.ten_goi}</td>
                <td>{Number(g.gia).toLocaleString()}đ</td>
                <td>{g.thoi_han} ngày</td>
                <td>
                  {g.trang_thai === "hoat_dong" ? (
                    <span className="px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-semibold">
                      Hoạt động
                    </span>
                  ) : (
                    <span className="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-sm font-semibold">
                      Ngừng bán
                    </span>
                  )}
                </td>

                <td className="flex gap-3 justify-center">
                  <button
                    className="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 hover:bg-blue-200 transition font-medium"
                    onClick={() => {
                      setSelected(g);
                      setForm(g);
                      setIsEditOpen(true);
                    }}
                  >
                    ✏️ Sửa
                  </button>

                  <button
                    className="px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition font-medium"
                    onClick={() => setDeleteId(g.id)}
                  >
                    🗑 Xóa
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal thêm */}
      {isAddOpen && (
        <Modal title="Thêm gói dịch vụ" onClose={() => setIsAddOpen(false)}>
          <PackageForm form={form} setForm={setForm} />
          <div className="flex justify-end gap-3 mt-6">
            <button className="btn" onClick={() => setIsAddOpen(false)}>
              Hủy
            </button>
            <button className="btn btn-success" onClick={handleAdd}>
              Thêm
            </button>
          </div>
        </Modal>
      )}

      {/* Modal sửa */}
      {isEditOpen && (
        <Modal title="Sửa gói dịch vụ" onClose={() => setIsEditOpen(false)}>
          <PackageForm form={form} setForm={setForm} />
          <div className="flex justify-end gap-3 mt-6">
            <button className="btn" onClick={() => setIsEditOpen(false)}>
              Hủy
            </button>
            <button className="btn btn-info" onClick={handleEdit}>
              Cập nhật
            </button>
          </div>
        </Modal>
      )}

      {/* Modal xóa */}
      {deleteId && (
        <Modal title="Xác nhận xóa gói" onClose={() => setDeleteId(null)}>
          <p className="mb-4 text-gray-700">
            Bạn có chắc muốn xóa gói dịch vụ này không? Hành động này không thể hoàn tác.
          </p>

          <div className="flex justify-end gap-3 mt-6">
            <button className="btn" onClick={() => setDeleteId(null)}>
              Hủy
            </button>

            <button
              className="btn btn-error"
              onClick={() => {
                handleDelete(deleteId);
                setDeleteId(null);
              }}
            >
              🗑 Xóa
            </button>
          </div>
        </Modal>
      )}
    </div>
  );
}

/* ✅ Form nhập liệu */
function PackageForm({ form, setForm }) {
  return (
    <div className="space-y-4">
      <input
        type="text"
        placeholder="Tên gói"
        className="input input-bordered w-full rounded-xl"
        value={form.ten_goi}
        onChange={(e) => setForm({ ...form, ten_goi: e.target.value })}
      />

      <textarea
        placeholder="Mô tả"
        className="textarea textarea-bordered w-full rounded-xl"
        value={form.mo_ta}
        onChange={(e) => setForm({ ...form, mo_ta: e.target.value })}
      />

      <input
        type="number"
        placeholder="Giá"
        className="input input-bordered w-full rounded-xl"
        value={form.gia}
        onChange={(e) => setForm({ ...form, gia: e.target.value })}
      />

      <input
        type="number"
        placeholder="Thời hạn (ngày)"
        className="input input-bordered w-full rounded-xl"
        value={form.thoi_han}
        onChange={(e) => setForm({ ...form, thoi_han: e.target.value })}
      />

      <select
        className="select select-bordered w-full rounded-xl"
        value={form.trang_thai}
        onChange={(e) => setForm({ ...form, trang_thai: e.target.value })}
      >
        <option value="hoat_dong">Hoạt động</option>
        <option value="ngung_ban">Ngừng bán</option>
      </select>
    </div>
  );
}

/* ✅ Modal đẹp */
function Modal({ title, children, onClose }) {
  return (
    <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
      <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg animate-fadeIn">
        <h3 className="font-bold text-2xl mb-4 text-gray-900">{title}</h3>
        {children}
        <button className="modal-backdrop" onClick={onClose}></button>
      </div>
    </div>
  );
}

/* ✅ Toast đẹp */
function ToastContainer() {
  if (!window.toast) {
    window.toast = {
      show: (msg, type = "info") => {
        const div = document.createElement("div");

        const colors = {
          success: "bg-green-500",
          error: "bg-red-500",
          warning: "bg-yellow-500",
          info: "bg-blue-500",
        };

        const icons = {
          success: "✅",
          error: "❌",
          warning: "⚠️",
          info: "ℹ️",
        };

        div.className = `
          flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-white
          ${colors[type] || colors.info}
          animate-slide-in
          mb-3
        `;

        div.innerHTML = `
          <span class="text-xl">${icons[type]}</span>
          <span class="font-medium">${msg}</span>
        `;

        const root = document.getElementById("toast-root");
        root.appendChild(div);

        setTimeout(() => {
          div.classList.add("animate-slide-out");
          setTimeout(() => div.remove(), 300);
        }, 2500);
      },
    };
  }

  return (
    <div id="toast-root" className="fixed top-5 right-5 z-[9999] flex flex-col items-end"></div>
  );
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
