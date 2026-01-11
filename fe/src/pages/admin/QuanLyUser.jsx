// src/pages/admin/QuanLyUser.jsx (hoặc tên file tương ứng)
import { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function QuanLyUser() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true); // Loading khi tải danh sách
  const [actionLoading, setActionLoading] = useState({}); // Loading cho từng user khi khóa/mở
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({});
  const [selectedUser, setSelectedUser] = useState(null);
  const [viewUser, setViewUser] = useState(null);
  const [searchInput, setSearchInput] = useState("");



  useEffect(() => {
    fetchUsers();
  }, [page, search]);
  useEffect(() => {
  const delay = setTimeout(() => {
    setSearch(searchInput);
  }, 400); // 400ms là đẹp

  return () => clearTimeout(delay);
}, [searchInput]);


  const fetchUsers = async () => {
    try {
      setLoading(true);
      const res = await axiosPrivate.get("/admin/users", {
        params: { search, page },
      });

      setUsers(res.data.data || []);
      setMeta({
        total: res.data.total,
        current: res.data.current_page,
        last: res.data.last_page,
      });
    } catch (err) {
      console.error(err);
      showToast("Không thể tải danh sách người dùng", "error");
    } finally {
      setLoading(false);
    }
  };

  const updateStatus = async (id, status) => {
    if (actionLoading[id]) return;

    setActionLoading((prev) => ({ ...prev, [id]: true }));

    try {
      await axiosPrivate.put(`/admin/user/${id}/status`, { status });

      showToast(
        status === "locked"
          ? "Đã khóa tài khoản thành công!"
          : "Đã mở khóa tài khoản thành công!",
        "success"
      );

      fetchUsers(); // Refresh danh sách
      setSelectedUser(null);
    } catch (err) {
      showToast("Lỗi cập nhật trạng thái!", "error");
    } finally {
      setActionLoading((prev) => {
        const newState = { ...prev };
        delete newState[id];
        return newState;
      });
    }
  };

  return (
    <div className="p-6 relative">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-6 text-gray-800 tracking-tight">
        👥 Quản lý người dùng
      </h1>

      {/* Search */}
      <div className="relative mb-6">
        <input
  type="text"
  placeholder="🔍 Tìm kiếm theo tên, email, số điện thoại..."
  className="w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition"
  value={searchInput}
  onChange={(e) => {
    setSearchInput(e.target.value);
    setPage(1);
  }}
/>
      </div>

      {/* Table */}
      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 bg-white rounded-2xl shadow">
          <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
          <p className="text-lg text-gray-600 font-medium">Đang tải danh sách người dùng...</p>
        </div>
      ) : (
        <>
          <div className="overflow-x-auto bg-white rounded-2xl shadow-lg border border-gray-100">
            <table className="table table-zebra w-full">
              <thead className="bg-gray-50 text-gray-700 font-semibold">
                <tr>
                  <th>Tên</th>
                  <th>Email</th>
                  <th>SĐT</th>
                  <th>Vai trò</th>
                  <th>Trạng thái</th>
                  <th className="text-center">Hành động</th>
                </tr>
              </thead>

              <tbody>
                {users.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="text-center py-10 text-gray-500">
                      Không tìm thấy người dùng nào
                    </td>
                  </tr>
                ) : (
                  users.map((u) => (
                    <tr key={u.id} className="hover:bg-gray-50 transition">
                      <td className="font-medium">{u.name}</td>
                      <td>{u.email}</td>
                      <td>{u.phone || "Chưa cập nhật"}</td>
                      <td>
                        <span className="px-3 py-1 rounded-lg bg-blue-100 text-blue-700 text-sm font-medium">
                          {u.role}
                        </span>
                      </td>
                      <td>
                        {u.status === "active" ? (
                          <span className="px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-semibold">
                            Hoạt động
                          </span>
                        ) : (
                          <span className="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-sm font-semibold">
                            Đã khóa
                          </span>
                        )}
                      </td>
                      <td className="text-center">
  {u.role === "admin" ? (
    <span className="text-gray-400 italic">Không thao tác</span>
  ) : (
    <div className="flex justify-center gap-2">
      {/* Nút xem */}
      <button
        className="px-3 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white transition"
        onClick={() => setViewUser(u)}
      >
        👁 Xem
      </button>

      {/* Nút khóa / mở */}
      <button
        className={`px-3 py-2 rounded-xl text-white font-medium transition min-w-[90px] ${
          u.status === "active"
            ? "bg-red-600 hover:bg-red-700"
            : "bg-green-600 hover:bg-green-700"
        }`}
        onClick={() => setSelectedUser(u)}
        disabled={!!actionLoading[u.id]}
      >
        {actionLoading[u.id] ? (
          <span className="loading loading-spinner loading-sm"></span>
        ) : u.status === "active" ? (
          "Khóa"
        ) : (
          "Mở"
        )}
      </button>
    </div>
  )}
</td>

                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          <div className="flex justify-center mt-6 gap-3">
            <button
              className="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
              disabled={page === 1 || loading}
              onClick={() => setPage(page - 1)}
            >
              «
            </button>

            <span className="px-4 py-2 rounded-xl bg-gray-100 shadow text-gray-700 font-medium">
              Trang {meta.current || page} / {meta.last || "?"}
            </span>

            <button
              className="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
              disabled={page === meta.last || loading}
              onClick={() => setPage(page + 1)}
            >
              »
            </button>
          </div>
        </>
      )}

      {/* Modal xác nhận */}
      {selectedUser && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
          <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md animate-fadeIn">
            <h3 className="font-bold text-xl mb-3 text-gray-900">
              {selectedUser.status === "active"
                ? "Khóa tài khoản?"
                : "Mở khóa tài khoản?"}
            </h3>

            <p className="mb-4 text-gray-700">
              Bạn có chắc muốn thay đổi trạng thái của{" "}
              <b>{selectedUser.name}</b> không?
            </p>

            <div className="flex justify-end gap-3 mt-5">
              <button
                className="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
                onClick={() => setSelectedUser(null)}
                disabled={actionLoading[selectedUser.id]}
              >
                Hủy
              </button>

              <button
                className={`px-5 py-2 rounded-xl text-white font-medium transition min-w-[120px] ${
                  selectedUser.status === "active"
                    ? "bg-red-600 hover:bg-red-700"
                    : "bg-green-600 hover:bg-green-700"
                }`}
                onClick={() =>
                  updateStatus(
                    selectedUser.id,
                    selectedUser.status === "active" ? "locked" : "active"
                  )
                }
                disabled={actionLoading[selectedUser.id]}
              >
                {actionLoading[selectedUser.id] ? (
                  <span className="loading loading-spinner loading-sm"></span>
                ) : (
                  "Xác nhận"
                )}
              </button>
            </div>
          </div>
        </div>
      )}
      {/* Modal xem thông tin user */}
{viewUser && (
  <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
    <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg animate-fadeIn">
      <h3 className="text-2xl font-bold mb-4 text-gray-900">
        👤 Thông tin người dùng
      </h3>

      <div className="space-y-3 text-gray-700">
        <InfoRow label="Họ tên" value={viewUser.name} />
        <InfoRow label="Email" value={viewUser.email} />
        <InfoRow label="Số điện thoại" value={viewUser.phone || "Chưa cập nhật"} />
        <InfoRow label="Vai trò" value={viewUser.role} />
        <InfoRow
          label="Trạng thái"
          value={
            viewUser.status === "active" ? "Hoạt động" : "Đã khóa"
          }
        />
        <InfoRow
          label="Ngày tạo"
          value={viewUser.created_at || "Không rõ"}
        />
      </div>

      <div className="flex justify-end mt-6">
        <button
          className="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
          onClick={() => setViewUser(null)}
        >
          Đóng
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
        }, 3500);
      },
    };
  }

  return (
    <div
      id="toast-root"
      className="fixed top-5 right-5 z-[9999] flex flex-col items-end gap-2"
    ></div>
  );
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
function InfoRow({ label, value }) {
  return (
    <div className="flex justify-between border-b pb-2">
      <span className="font-medium text-gray-600">{label}</span>
      <span className="font-semibold text-gray-900">{value}</span>
    </div>
  );
}
