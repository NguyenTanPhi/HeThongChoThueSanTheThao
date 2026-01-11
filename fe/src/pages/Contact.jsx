export default function Contact() {
  return (
    <div className="max-w-5xl mx-auto px-6 py-10">
      <h1 className="text-4xl font-bold text-success mb-6 flex items-center gap-3">
        <span className="text-5xl">📞</span> Liên hệ
      </h1>

      <div className="bg-base-100 shadow-lg p-8 rounded-xl">

        {/* Thông tin liên hệ */}
        <h2 className="text-2xl font-semibold mb-6">Thông tin hỗ trợ</h2>

        <div className="space-y-5 text-lg">
          <p>
            📧 Email hỗ trợ:  
            <span className="font-semibold"> dh52111486@student.stu.edu.vn</span>
          </p>

          <p>
            📱 Số điện thoại:  
            <span className="font-semibold"> 0703760626</span>
          </p>

          <p>
            📍 Địa chỉ:  
            <span className="font-semibold"> 180 Cao Lỗ, Phường 4, Quận 8, TP. Hồ Chí Minh</span>
          </p>

          <p>
            🕒 Thời gian làm việc:  
            <span className="font-semibold"> 24/7</span>
          </p>
        </div>

        {/* Mạng xã hội */}
        <h2 className="text-2xl font-semibold mt-10 mb-4">Kết nối với chúng tôi</h2>

        <div className="flex flex-col gap-3 mt-6">

  <a
    href="https://www.facebook.com/phi.796171/"
    target="_blank"
    rel="noopener noreferrer"
    className="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-sm transition text-base w-max"
  >
    <span className="text-xl">📘</span>
    Facebook
  </a>

  <a
    href="https://zalo.me/0703760626"
    target="_blank"
    rel="noopener noreferrer"
    className="flex items-center gap-2 bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-sm transition text-base w-max"
  >
    <span className="text-xl">💬</span>
    Zalo
  </a>

</div>

      </div>
    </div>
  );
}
