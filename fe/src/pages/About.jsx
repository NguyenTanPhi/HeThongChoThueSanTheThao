export default function About() {
  return (
    <div className="max-w-5xl mx-auto px-6 py-10">
      {/* Tiêu đề */}
      <h1 className="text-4xl font-bold text-success mb-6 flex items-center gap-3">
        <span className="text-5xl">📘</span> Giới thiệu về hệ thống
      </h1>

      {/* Nội dung */}
      <div className="bg-base-100 shadow-lg p-8 rounded-xl leading-relaxed">
        <p className="mb-4 text-lg">
          Hệ thống <span className="font-semibold text-success"> Đặt Sân Thể Thao </span> 
          được xây dựng nhằm hỗ trợ người dùng dễ dàng tìm kiếm, đặt sân và quản lý lịch đá bóng 
          một cách nhanh chóng, minh bạch và thuận tiện.
        </p>

        <p className="mb-4 text-lg">
          Đây là đề tài thuộc luận văn tốt nghiệp, được phát triển bằng 
          <span className="font-semibold"> ReactJS, Lavarel và MySQL</span>, 
          kết hợp giao diện hiện đại, thân thiện với người dùng.
        </p>

        <p className="mb-4 text-lg">
          Hệ thống cung cấp các chức năng như:
        </p>

        <ul className="list-disc ml-6 text-lg space-y-2">
          <li>Tìm kiếm và xem thông tin các sân bóng.</li>
          <li>Đặt sân theo khung giờ mong muốn.</li>
          <li>Quản lý lịch đặt sân của người dùng.</li>
          <li>Quản trị sân bóng dành cho chủ sân.</li>
          <li>Đánh giá và phản hồi chất lượng sân.</li>
        </ul>

        <p className="mt-6 text-lg">
          Mục tiêu của hệ thống là tối ưu hóa trải nghiệm của người dùng, 
          giảm thời gian đặt sân và giúp chủ sân quản lý hiệu quả hơn.
        </p>
      </div>
    </div>
  );
}
