import React from "react";
import { Link } from "react-router-dom";

export default function Footer() {
  return (
    <footer className="bg-gray-900 text-gray-300 mt-16 pt-12 pb-6">
      <div className="container mx-auto px-6">
        
        {/* GRID 3 CỘT */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">
          
          {/* CỘT 1 - LOGO & MÔ TẢ */}
          <div>
            <h2 className="text-3xl font-bold text-green-500 mb-3">
              Đặt Sân Thể Thao
            </h2>
            <p className="text-gray-400 leading-relaxed">
              Hệ thống đặt sân thể thao hiện đại – nhanh chóng – tiện lợi.
              Chúng tôi hỗ trợ bạn tìm và đặt sân tốt nhất chỉ trong vài giây.
            </p>
          </div>

          {/* CỘT 2 - ĐIỀU HƯỚNG */}
          <div>
            <h3 className="text-xl font-semibold text-white mb-4">Liên kết</h3>
            <ul className="space-y-2 text-gray-400">
              <li>
                <Link to="/" className="hover:text-white">🏠 Trang chủ</Link>
              </li>
              <li>
                <Link to="/about" className="hover:text-white">ℹ️ Giới thiệu</Link>
              </li>
              <li>
                <Link to="/contact" className="hover:text-white">📞 Liên hệ</Link>
              </li>
              <li>
                <Link to="/login" className="hover:text-white">🔐 Đăng nhập</Link>
              </li>
            </ul>
          </div>

          {/* CỘT 3 - LIÊN HỆ */}
          <div>
            <h3 className="text-xl font-semibold text-white mb-4">Hỗ trợ</h3>
            <ul className="space-y-2 text-gray-400">
              <li>📍 180 Cao Lỗ, Quận 8, TP.HCM</li>
              <li>📧 nq2018.nguyentanphi311@gmail.com</li>
              <li>📱 0703 760 626</li>
              <li className="flex items-center gap-3 mt-3">
                <a
                  href="https://zalo.me/0703760626"
                  target="_blank"
                  className="bg-white text-blue-500 px-3 py-1 rounded-full shadow hover:bg-gray-100 text-sm"
                >
                  Zalo
                </a>
                <a
                  href="https://facebook.com"
                  target="_blank"
                  className="bg-blue-600 text-white px-3 py-1 rounded-full shadow hover:bg-blue-700 text-sm"
                >
                  Facebook
                </a>
              </li>
            </ul>
          </div>
        </div>

        {/* COPYRIGHT */}
        <div className="border-t border-gray-700 pt-4 text-center text-gray-500 text-sm">
          © 2025 Đặt Sân Thể Thao. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
