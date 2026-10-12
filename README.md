# GearHub – Hệ thống thương mại điện tử phụ kiện công nghệ

> Đồ án môn học Phát triển Ứng dụng Web - Trường Đại học Công nghệ Thông tin (UIT)

## 👥 Thành viên nhóm
- **Thành viên A (Nhóm trưởng):** Lead Frontend & Luồng khách hàng (Storefront, Breeze, Giỏ hàng, Checkout)
- **Thành viên B:** Lead Backend, Database & Quản trị (Migrations, Models, Seeders, Admin CRUD, OrderService)

## 🛠 Công nghệ sử dụng
- **Backend:** Laravel 11/12 (PHP 8.2+)
- **Database:** MySQL 8.0+
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Chart:** Chart.js
- **Auth:** Laravel Breeze (Blade)

---

## 🎨 Design Tokens (Figma)
- **Primary:** `#2563EB` (Xanh chủ đạo)
- **Secondary:** `#1E293B` (Navy đậm)
- **Accent/Action:** `#F97316` (Cam nút hành động)
- **Surface / Background:** `#F8FAFC`
- **Heading / Text:** `#0F172A`
- **Typography:** `Inter`

---

## 🚀 Hướng dẫn cài đặt từ đầu (Clean Setup)

Sau khi clone repo về máy, thực hiện các lệnh sau:

1. **Cài đặt thư viện:**
   ```bash
   composer install
   npm install
   ```

2. **Cấu hình môi trường:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Kiểm tra thông tin database `gearhub_db` trong file `.env`)*

3. **Chạy Migration và nạp dữ liệu mẫu:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Tạo Symbolic Link cho ảnh:**
   ```bash
   php artisan storage:link
   ```

5. **Khởi chạy ứng dụng:**
   - Terminal 1 (Build assets):
     ```bash
     npm run dev
     ```
   - Terminal 2 (Local server):
     ```bash
     php artisan serve
     ```

Truy cập: `http://localhost:8000`

---

## 📌 Quy ước Git
- Nhánh `main`: Bản ổn định, chỉ merge ở các mốc quan trọng.
- Nhánh `develop`: Nhánh làm việc và tích hợp chính.
- Nhánh tính năng: `feature/<ten-chuc-nang>`
- Tuân thủ **Conventional Commits**: `feat(...)`, `fix(...)`, `docs(...)`, `refactor(...)`, `test(...)`.
- Mọi thay đổi vào `develop` đều đi qua Pull Request và được thành viên còn lại review.
