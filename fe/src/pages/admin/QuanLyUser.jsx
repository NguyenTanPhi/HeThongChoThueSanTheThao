import { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function QuanLyUser() {
  const [users, setUsers] = useState([]);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({});
  const [selectedUser, setSelectedUser] = useState(null);

  useEffect(() => {
    fetchUsers();
  }, [page, search]);

  const fetchUsers = async () => {
    try {
      const res = await axiosPrivate.get("/admin/users", {
        params: { search, page }
      });

      setUsers(res.data.data);
      setMeta({
        total: res.data.total,
        current: res.data.current_page,
        last: res.data.last_page,
      });
    } catch (err) {
      console.error(err);
      showToast("Không thể tải danh sách người dùng", "error");
    }
  };

  const updateStatus = async (id, status) => {
    try {
      await axiosPrivate.put(`/admin/user/${id}/status`, { status });

      showToast(
        status === "locked"
          ? "Đã khóa tài khoản!"
          : "Đã mở khóa tài khoản!",
        "success"
      );

      fetchUsers();
      setSelectedUser(null);
    } catch (err) {
      showToast("Lỗi cập nhật trạng thái!", "error");
    }
  };

  return (
    <div className="p-6">
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
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
        />
      </div>

      {/* Table */}
      <div className="overflow-x-auto bg-white rounded-2xl shadow-lg border border-gray-100">
        <table className="table table-zebra">
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
            {users.map((u) => (
              <tr key={u.id} className="hover:bg-gray-50 transition">
                <td className="font-medium">{u.name}</td>
                <td>{u.email}</td>
                <td>{u.phone}</td>
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
                    <button
                      className={`px-4 py-2 rounded-xl text-white font-medium transition ${
                        u.status === "active"
                          ? "bg-red-600 hover:bg-red-700"
                          : "bg-green-600 hover:bg-green-700"
                      }`}
                      onClick={() => setSelectedUser(u)}
                    >
                      {u.status === "active" ? "Khóa" : "Mở khóa"}
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      <div className="flex justify-center mt-6 gap-3">
        <button
          className="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
          disabled={page === 1}
          onClick={() => setPage(page - 1)}
        >
          «
        </button>

        <span className="px-4 py-2 rounded-xl bg-gray-100 shadow text-gray-700 font-medium">
          Trang {meta.current} / {meta.last}
        </span>

        <button
          className="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition"
          disabled={page === meta.last}
          onClick={() => setPage(page + 1)}
        >
          »
        </button>
      </div>

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
              >
                Hủy
              </button>

              <button
                className={`px-5 py-2 rounded-xl text-white font-medium transition ${
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
              >
                Xác nhận
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
        }, 2500);
      },
    };
  }

  return (
    <div
      id="toast-root"
      className="fixed top-5 right-5 z-[9999] flex flex-col items-end"
    ></div>
  );
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
