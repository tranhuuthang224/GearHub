# GearHub – Hệ thống thương mại điện tử phụ kiện công nghệ & linh kiện máy tính

<p align="center">
  <img src="public/images/logo.png" alt="GearHub Logo" width="160" onerror="this.style.display='none'"/>
</p>

<p align="center">
  <strong>Đồ án môn học Phát triển Ứng dụng Web</strong><br>
  Khoa Hệ thống Thông tin – Trường Đại học Công nghệ Thông tin, ĐHQG-HCM (UIT)
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+"/>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js"/>
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0+"/>
  <img src="https://img.shields.io/badge/Tests-283%20Passed-brightgreen?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Tests"/>
  <img src="https://img.shields.io/badge/Deploy-Railway_Cloud-0B0D0E?style=for-the-badge&logo=railway&logoColor=white" alt="Railway"/>
</p>

---

## 👥 Thông tin học phần & Nhóm phát triển

* **Trường:** Đại học Công nghệ Thông tin – ĐHQG TP.HCM (UIT)
* **Khoa:** Hệ thống Thông tin (Faculty of Information Systems)
* **Môn học:** Phát triển Ứng dụng Web (Web Application Development)
* **Giảng viên hướng dẫn:** ThS. Nguyễn Thanh Bình

| STT | Họ và tên | MSSV | Vai trò chính trong dự án |
|:---:|:---|:---:|:---|
| 1 | **Trần Hữu Thắng** *(Nhóm trưởng)* | **24521614** | **Lead Frontend & Luồng khách hàng:** Thiết kế UI/UX Figma, Storefront, Laravel Breeze Skin đỏ trắng, Bộ lọc sản phẩm, Giỏ hàng Session, Checkout, So sánh thông số (Compare Bar), Trợ lý Setup Wizard, Góc máy cộng đồng (Pinterest UI). |
| 2 | **Ngô Hồng Ân** | **24250052** | **Lead Backend, Database & Quản trị:** Thiết kế Schema CSDL chuẩn 3NF (25 Migrations, 24 Tables), Eloquent Models & Seeders, OrderService (DB Transaction + Pessimistic Locking), Phân hệ Admin CRUD & Thùng rác Soft Delete, Tích hợp VietQR, Viết 283 Automated Tests. |

---

## 🛠 Công nghệ & Thư viện sử dụng (Tech Stack)

### 1. Backend & Core Architecture
* **Ngôn ngữ & Framework:** PHP 8.3+ | Laravel 12.x
* **Kiến trúc hệ thống:** Layered Architecture (*Controller → Form Request → Service Layer → Eloquent Model*)
* **Cơ sở dữ liệu:** MySQL 8.0+ (InnoDB Engine, khóa ngoại quan hệ chặt chẽ, tối ưu hóa Indexing và cấu trúc JSON specs linh hoạt)
* **Xác thực & Bảo mật (Auth & Security):** 
  * Laravel Breeze (Blade Skin) xác thực Session-based an toàn.
  * Google OAuth 2.0 Đăng nhập nhanh qua **Laravel Socialite (^5.33)**.
  * Phân quyền vai trò người dùng (RBAC) với Middleware `role:admin`, `role:customer`.
  * Chống CSRF Token, XSS Clean, SQL Injection (PDO Prepared Statements), Bcrypt hashing.
* **Xử lý giao dịch & Chống Race Condition:**
  * `DB::transaction()` kết hợp **Pessimistic Locking (`lockForUpdate()`)** bảo vệ số lượng tồn kho sản phẩm và hạn mức mã giảm giá khi hàng ngàn yêu cầu đặt hàng xảy ra đồng thời.
* **Cổng thanh toán & Dịch vụ ngoài:**
  * **VietQR Service:** Sinh mã QR chuyển khoản ngân hàng động theo chuẩn EMVCo tự động khớp nội dung chuyển khoản và số tiền đơn hàng.

### 2. Frontend & User Interface
* **Giao diện:** Laravel Blade Component-driven Architecture.
* **CSS Framework:** Tailwind CSS 3.x với cấu hình mở rộng Design Tokens tùy chỉnh theo Figma.
* **Tương tác động (Interactivity):** Alpine.js 3.x (Dropdowns, Modals, Drawer, Toast notifications, Sticky Compare Bar, Image Carousels).
* **Biểu đồ & Báo cáo:** Chart.js tích hợp trực quan hóa doanh thu và đơn hàng trong Admin Dashboard.
* **Icons:** Heroicons & Lucide Icons.

### 3. Hạ tầng Cloud & DevOps
* **Nền tảng Cloud:** Railway Cloud PaaS (Tự động hóa CI/CD qua GitHub Webhooks).
* **Server Runtime:** Docker Container chạy FrankenPHP (Caddy Server tích hợp worker mode siêu tốc).
* **Tên miền & SSL:** Tên miền quốc gia `https://www.gearhub.io.vn` / `https://gearhub.io.vn` tích hợp HTTPS/TLS tự động.

---

## 🎨 Design Tokens chuẩn Figma (Figma Design System)

Hệ thống giao diện GearHub được đồng bộ 100% với file thiết kế Figma thông qua `tailwind.config.js`:

| Token Name | Mã màu HEX | Ứng dụng trong giao diện |
|:---|:---:|:---|
| **Primary (Đỏ công nghệ)** | `#E5252A` | Màu thương hiệu chủ đạo, nút mua ngay, badge giảm giá, active state *(Hover: `#CC1E23`)* |
| **Secondary (Graphite tối)** | `#18181B` | Thanh điều hướng Admin, Footer công nghệ cao, nút phụ tương phản |
| **Surface (Trắng tinh khiết)** | `#FFFFFF` | Nền thẻ sản phẩm (Card), Modal, Popover |
| **Surface Subtle** | `#F4F5F7` | Nền ô nhập liệu Form, hộp thông số kỹ thuật, thẻ phụ |
| **Surface Muted** | `#F8FAFC` | Màu nền xám dịu mắt toàn trang web |
| **Heading & Text** | `#09090B` | Màu chữ tiêu đề và nội dung chính, đảm bảo độ tương phản chuẩn WCAG 2.1 |
| **Typography** | `Inter` | Phông chữ không chân hiện đại, tối ưu hiển thị số liệu kỹ thuật |

---

## ✨ Các tính năng nổi bật của GearHub

### 🛒 Dành cho Khách hàng (Storefront & Customer Journey)
1. **Trang chủ & Danh mục phân cấp:** Banner sự kiện, danh mục phần cứng linh kiện phân cấp 2 tầng (Bàn phím cơ, Chuột Gaming, Tai nghe & Loa, Màn hình máy tính).
2. **Bộ lọc sản phẩm thông minh:** Lọc đồng thời theo Danh mục con, Thương hiệu, Khoảng giá ngân sách, Tình trạng còn hàng và Sắp xếp linh hoạt.
3. **Chi tiết sản phẩm chuyên sâu:** Xem thư viện ảnh, thông số kỹ thuật chi tiết lưu trữ dạng JSON có cấu trúc, tình trạng kho theo thời gian thực.
4. **⚖️ So sánh thông số kỹ thuật (Spec Comparison):** Cho phép chọn tối đa 4 sản phẩm cùng lúc, thanh nổi **Sticky Compare Bar** dưới đáy màn hình xuất hiện mượt mà và bảng đối soát từng thông số kỹ thuật song song.
5. **🧙 Trợ lý tư vấn cấu hình (Setup Wizard):** Hướng dẫn 3 bước trực quan giúp người mới chọn combo gear phù hợp nhất dựa trên nhu cầu (Esports, Làm việc văn phòng, Lập trình) và ngân sách thực tế.
6. **📸 Góc máy cộng đồng (Community Showroom):** Mạng xã hội thu nhỏ cho dân công nghệ: Bố cục dạng Pinterest Masonry, cho phép người dùng đăng ảnh góc máy, gắn thẻ (tag) các gear có trong ảnh, thả tim (Like), thảo luận bình luận và quản lý bài viết của mình.
7. **Giỏ hàng Session & Mã giảm giá (Coupons):** Thêm nhanh vào giỏ hàng, cập nhật số lượng tức thì qua AJAX, áp dụng mã giảm giá phần trăm hoặc số tiền cố định với điều kiện đơn tối thiểu.
8. **Thanh toán đa phương thức (Checkout):** 
   * Tự động điền thông tin từ Sổ địa chỉ cá nhân (`address_book`).
   * Thanh toán khi nhận hàng (COD).
   * Thanh toán chuyển khoản ngân hàng tự động qua mã **VietQR**.
9. **Sổ địa chỉ & Theo dõi đơn hàng:** Quản lý nhiều địa chỉ nhận hàng, cờ mặc định `is_default`, tra cứu lịch sử mua hàng và hủy đơn hàng đang chờ duyệt.
10. **Đăng nhập linh hoạt:** Đăng nhập truyền thống với mật khẩu mã hóa Bcrypt hoặc **Đăng nhập 1-click bằng Google Account**.

### 👑 Dành cho Quản trị viên (Admin Panel)
1. **Dashboard điều hành thông minh:** Biểu đồ doanh thu Chart.js theo tháng, tổng số đơn hàng, đếm người dùng, widget cảnh báo các sản phẩm sắp hết hàng (*Tồn kho $\le 5$*).
2. **Quản lý danh mục & Thương hiệu:** Thêm, sửa, xóa danh mục cha-con và thương hiệu với tạo slug tự động.
3. **Quản lý sản phẩm & Thùng rác (Soft Delete):** CRUD sản phẩm toàn diện, tải nhiều ảnh, nhập thông số kỹ thuật dạng linh hoạt, tính năng **Thùng rác** cho phép Khôi phục (Restore) hoặc Xóa vĩnh viễn (Force Delete).
4. **Quản lý đơn hàng (Order Management):** Cập nhật trạng thái đơn (*Chờ xử lý → Đã xác nhận → Đang giao → Hoàn thành → Đã hủy*), tự động hoàn lại số lượng tồn kho khi đơn bị hủy.
5. **Quản lý mã giảm giá (Coupon Manager):** Thiết lập mã, loại giảm giá (% hoặc VNĐ), giá trị giảm, giá trị đơn tối thiểu, giới hạn tổng lượt dùng và thời hạn hiệu lực.
6. **Kiểm duyệt bài viết góc máy:** Duyệt hoặc từ chối các bài đăng chia sẻ góc máy từ người dùng trước khi hiển thị công khai.

---

## 🚀 Hướng dẫn cài đặt từ đầu (Clean Setup)

Sau khi clone repository về máy tính, thực hiện các bước sau trong terminal:

### 1. Cài đặt các gói phụ thuộc (Dependencies)
```bash
composer install
npm install
```

### 2. Thiết lập môi trường (.env)
```bash
cp .env.example .env
php artisan key:generate
```
> Cấu hình thông tin kết nối CSDL MySQL trong file `.env`:
> ```env
> DB_CONNECTION=mysql
> DB_HOST=127.0.0.1
> DB_PORT=3306
> DB_DATABASE=gearhub_db
> DB_USERNAME=root
> DB_PASSWORD=
> ```

### 3. Chạy Migration và nạp dữ liệu mẫu
```bash
php artisan migrate:fresh --seed
```
*Lệnh này sẽ khởi tạo toàn bộ 24 bảng CSDL và nạp sẵn 40+ sản phẩm công nghệ thực tế, danh mục phân cấp, tài khoản demo và mã giảm giá.*

### 4. Tạo Symbolic Link cho thư mục lưu trữ ảnh
```bash
php artisan storage:link
```

### 5. Khởi chạy ứng dụng
Mở 2 terminal song song:
* **Terminal 1 (Biên dịch Frontend Vite):**
  ```bash
  npm run dev
  ```
* **Terminal 2 (Máy chủ Laravel Local):**
  ```bash
  php artisan serve
  ```

👉 Mở trình duyệt truy cập: **`http://localhost:8000`**

---

## 🔑 Tài khoản thử nghiệm (Demo Credentials)

| Vai trò (Role) | Địa chỉ Email | Mật khẩu mặc định | Quyền hạn |
|:---|:---|:---:|:---|
| **Quản trị viên (Admin)** | `admin@gearhub.vn` | `Admin@123456` | Toàn quyền quản trị phân hệ `/admin/*` |
| **Khách hàng 1** | `khach1@gearhub.vn` | `Customer@123` | Mua hàng, sổ địa chỉ, đăng bài góc máy |
| **Khách hàng 2** | `khach2@gearhub.vn` | `Customer@123` | Khách hàng mua sắm |
| **Khách hàng 3** | `khach3@gearhub.vn` | `Customer@123` | Khách hàng mua sắm |

---

## 🎟️ Mã giảm giá thử nghiệm (Demo Coupons)

| Mã Coupon | Loại giảm giá | Điều kiện áp dụng | Mục đích thử nghiệm |
|:---|:---:|:---|:---|
| **`GEAR10`** | Giảm 10% | Đơn hàng từ 500.000 ₫ | Kiểm tra giảm giá theo tỷ lệ phần trăm |
| **`SAVE50K`** | Giảm 50.000 ₫ | Đơn hàng từ 300.000 ₫ | Kiểm tra giảm giá số tiền cố định |
| **`EXPIRED`** | Giảm 20% | Đã hết hạn sử dụng | Kiểm tra bắt lỗi mã giảm giá hết hạn |
| **`FULL`** | Giảm 50% | Đã hết số lượt sử dụng | Kiểm tra bắt lỗi mã đã dùng hết quota |
| **`SOON`** | Giảm 15% | Chưa đến ngày bắt đầu | Kiểm tra bắt lỗi mã chưa có hiệu lực |
| **`DISABLED`** | Giảm 30% | Quản trị viên đã tắt | Kiểm tra bắt lỗi mã bị vô hiệu hóa |

---

## 🧪 Kiểm thử tự động (Automated Testing & Race Condition)

GearHub sở hữu bộ kiểm thử tự động toàn diện đảm bảo độ ổn định và an toàn nghiệp vụ tài chính:

### 1. Toàn bộ Test Suite (Feature & Unit Tests)
```bash
php artisan test
```
* **Kết quả đạt được:** **283 / 283 tests Passed (100%)** với hơn **1.187 Assertions** kiểm tra toàn bộ luồng Auth, Giỏ hàng, Mã giảm giá, Checkout, Đặt hàng, Soft Delete, Quyền hạn RBAC, Socialite Google Login.

### 2. Kiểm thử chống Race Condition trong môi trường đồng thời cao
Hệ thống cung cấp lệnh CLI chuyên dụng mô phỏng nhiều tiến trình đặt hàng cùng một mili-giây:
```bash
php artisan orders:race-test stock --workers=8 --stock=3
```
* **Cơ chế:** Kích hoạt 8 worker song song tranh chấp 3 sản phẩm duy nhất trong kho. Nhờ `DB::transaction()` và `lockForUpdate()`, hệ thống đảm bảo chính xác 3 đơn thành công và 5 đơn bị từ chối an toàn, tồn kho cuối cùng về đúng bằng 0 mà không bao giờ bị âm kho.
* Các kịch bản race-test khác: `multi` (chống Deadlock), `coupon` (chống vượt hạn mức mã), `cancel` (chống hoàn kho 2 lần).

---

## 📌 Quy ước Git & Luồng làm việc (Git Flow)

Dự án áp dụng mô hình Git Flow chặt chẽ và chuyên nghiệp:
* `main`: Nhánh triển khai Production ổn định, kết nối trực tiếp với Railway PaaS.
* `develop`: Nhánh tích hợp chính dùng để kiểm thử và phát triển chung của nhóm.
* `feature/<tên-tính-năng>`: Nhánh phát triển độc lập cho từng module.
* Tuân thủ quy chuẩn **Conventional Commits**:
  * `feat(...)`: Phát triển tính năng mới.
  * `fix(...)`: Sửa lỗi phát sinh.
  * `docs(...)`: Cập nhật tài liệu, README, báo cáo.
  * `style(...)`: Tinh chỉnh CSS, giao diện Figma, căn chỉnh bố cục.
  * `refactor(...)`: Tối ưu hóa cấu trúc mã nguồn.
  * `test(...)`: Bổ sung kiểm thử tự động.

---

<p align="center">
  <sub>Đồ án được thực hiện với tinh thần kỹ thuật cao bởi nhóm sinh viên Khoa Hệ thống Thông tin – Trường Đại học Công nghệ Thông tin (UIT)</sub><br>
  <sub>© 2026 GearHub. Mã nguồn mở phục vụ mục đích học tập và nghiên cứu.</sub>
</p>
