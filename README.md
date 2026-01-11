

#  Laravel & Vue.js E-commerce Platform

Website bán hàng trực tuyến Full-stack hiện đại, được xây dựng với **Backend Laravel (API)** và **Frontend Vue.js (SPA)**. Hệ thống tích hợp đầy đủ quy trình mua sắm, thanh toán trực tuyến qua VNPAY và gửi email xác nhận đơn hàng tự động.

##  Demo Trực Tiếp (Live Preview)

Truy cập website tại đây: **[https://laravel-api-zm85.onrender.com/](https://laravel-api-zm85.onrender.com/)**

> **Lưu ý:** Do sử dụng server miễn phí của Render, trang web có thể mất **30-60 giây** để khởi động trong lần truy cập đầu tiên. Vui lòng kiên nhẫn chờ đợi.

---

## Tài Khoản Truy Cập (Test Credentials)

Dưới đây là các tài khoản được tạo sẵn để trải nghiệm đầy đủ tính năng của website:

| Vai Trò (Role) | Email | Mật khẩu | Quyền Hạn |
| --- | --- | --- | --- |
| **Quản Trị Viên (Admin)** | `admin@gmail.com` | `123456` | Truy cập Dashboard, Thêm/Sửa/Xóa Sản phẩm, Quản lý Đơn hàng, Xem thống kê. |
| **Khách Hàng (User)** | `user@gmail.com` | `123456` | Mua hàng, Thanh toán, Xem lịch sử đơn hàng, Bình luận sản phẩm. |

---

## Thông Tin Thanh Toán Thử Nghiệm (VNPAY Sandbox)

Hệ thống tích hợp cổng thanh toán VNPAY (Môi trường kiểm thử - Sandbox). Vui lòng sử dụng thông tin thẻ dưới đây để thực hiện thanh toán giả lập (Không trừ tiền thật):

* **Ngân hàng:** `NCB`
* **Số thẻ:** `9704198526191432198`
* **Tên chủ thẻ:** `NGUYEN VAN A`
* **Ngày phát hành:** `07/15`
* **Mật khẩu OTP:** `123456`

---

##  Hệ Thống Email (Mailtrap)

Dự án sử dụng **Mailtrap** để giả lập gửi email xác nhận đơn hàng.

* Khi khách hàng đặt hàng thành công, hệ thống sẽ gửi email xác nhận về địa chỉ email của khách (Môi trường test: Email sẽ được hứng tại Mailtrap inbox của Admin).

---

##  Tính Năng Nổi Bật

### 1. Phía Người Dùng (Client)

* **Giao diện SPA:** Trải nghiệm mượt mà không cần load lại trang.
* **Tìm kiếm & Lọc:** Tìm sản phẩm theo tên, danh mục, giá tiền.
* **Giỏ hàng thông minh:** Thêm, sửa, xóa sản phẩm, tự động tính tổng tiền.
* **Thanh toán Online:** Tích hợp cổng thanh toán VNPAY an toàn.
* **Quản lý tài khoản:** Xem lịch sử mua hàng, trạng thái đơn hàng.

### 2. Phía Quản Trị (Admin Dashboard)

* **Thống kê Dashboard:** Tổng quan doanh thu, số lượng đơn hàng, sản phẩm, khách hàng.
* **Quản lý Sản phẩm:**
* Upload  ảnh sản phẩm .
* Quản lý tồn kho (Stock).
* **Quản lý Đơn hàng:** Xem chi tiết đơn hàng, cập nhật trạng thái (Chờ xử lý -> Đang giao -> Hoàn thành).
---

##  Công Nghệ Sử Dụng (Tech Stack)

### Backend

* **Laravel Framework:** RESTful API.
* **MySQL:** Cơ sở dữ liệu.
* **Laravel Sanctum:** Xác thực API (Authentication).
* **Intervention Image:** Xử lý hình ảnh.

### Frontend

* **Vue.js 3:** Composition API, Script Setup.
* **Vue Router:** Điều hướng trang.
* **Axios:** Kết nối API.
* **Bootstrap 5:** Giao diện Responsive.
* **Vue Toastification:** Thông báo popup đẹp mắt.

---

## ⚙️ Hướng Dẫn Cài Đặt (Localhost)

Nếu bạn muốn chạy dự án trên máy cá nhân:

1. **Clone dự án:**
```bash
...
```

2. **Cài đặt Backend:**
```bash

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve

```

3. **Cài đặt Frontend:**
```bash

npm install
npm run dev



---

*Cảm ơn bạn đã ghé thăm dự án! ❤️*
