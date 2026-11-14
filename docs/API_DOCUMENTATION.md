# GEARHUB RESTful API SPECIFICATION & PUBLIC DOCUMENTATION

> **Dự án:** GearHub E-Commerce & Community Platform  
> **Phiên bản API:** v1.0 (RESTful JSON)  
> **Base URL:** `http://localhost:8000` (hoặc `http://gearhub.test`)  
> **Định dạng dữ liệu:** `application/json`  
> **Authentication:** Cookie Session (`laravel_session`) & Bearer / CSRF Token  

---

## 📌 QUY ƯỚC MÃ LỖI HTTP STATUS CODES

| HTTP Status | Ý nghĩa | Mô tả |
| :--- | :--- | :--- |
| **`200 OK`** | Thành công | Request hợp lệ, trả về dữ liệu tương ứng. |
| **`201 Created`** | Tạo thành công | Bản ghi mới đã được tạo vào CSDL. |
| **`401 Unauthorized`** | Chưa đăng nhập | Yêu cầu phải xác thực tài khoản trước khi thực hiện. |
| **`403 Forbidden`** | Không có quyền | Bị chặn bởi phân quyền (Ví dụ: khách thường vào trang Admin). |
| **`404 Not Found`** | Không tìm thấy | ID, Slug hoặc đường dẫn không tồn tại trong hệ thống. |
| **`422 Unprocessable`** | Lỗi Dữ liệu / Nghiệp vụ | Sai định dạng validate hoặc vi phạm quy tắc nghiệp vụ (Hết hàng, khác danh mục...). |
| **`429 Too Many Requests`** | Quá giới hạn | Bị chặn bởi Rate Limiter chống spam request. |

---

## 1. PHÂN HỆ SẢN PHẨM & CỬA HÀNG (CATALOG & SHOP)

### 1.1. Lấy danh sách sản phẩm & Lọc đa tiêu chí
- **Endpoint:** `GET /shop`
- **Headers:** `Accept: application/json`
- **Query Parameters:**
  - `category` (string, optional): Slug danh mục (Lọc đệ quy cả danh mục con). Ví dụ: `ban-phim-co`.
  - `brand` (string, optional): Slug thương hiệu. Ví dụ: `logitech`.
  - `min_price` (integer, optional): Giá tối thiểu (VNĐ).
  - `max_price` (integer, optional): Giá tối đa (VNĐ).
  - `in_stock` (boolean, optional): `1` chỉ lấy hàng còn tồn kho.
  - `search` (string, optional): Từ khóa tìm kiếm theo tên hoặc SKU.
  - `sort` (string, optional): `price_asc`, `price_desc`, `latest`, `popular`.
  - `page` (integer, optional): Số trang (mặc định 12 sản phẩm/trang).
- **Response `200 OK`:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Bàn phím cơ Aula F75",
      "slug": "ban-phim-co-aula-f75",
      "sku": "KB-AULA-F75",
      "price": 1250000,
      "sale_price": 990000,
      "stock_quantity": 45,
      "thumbnail_url": "http://localhost:8000/images/Keyboard/Aula F75.webp",
      "specs": {
        "Layout": "75%",
        "Kết nối": "Type-C / 2.4G / Bluetooth",
        "Switch": "Reaper Switch",
        "Keycap": "PBT Double-shot",
        "Led": "RGB 16.8 triệu màu"
      }
    }
  ],
  "total": 91
}
```

### 1.2. Tìm kiếm nhanh sản phẩm phục vụ Tag Gear (Auto-complete)
- **Endpoint:** `GET /community/search-products`
- **Query Parameters:** `q` (string, required): Từ khóa tìm kiếm tên hoặc SKU.
- **Response `200 OK`:**
```json
[
  {
    "id": 1,
    "name": "Bàn phím cơ Aula F75",
    "brand": "Aula",
    "category": "Bàn phím cơ không dây",
    "thumbnail": "http://localhost:8000/images/Keyboard/Aula F75.webp",
    "effective_price": 990000,
    "formatted_price": "990.000₫",
    "in_stock": true
  }
]
```

---

## 2. PHÂN HỆ SO SÁNH SẢN PHẨM (COMPARE ENGINE)

### 2.1. Lấy dữ liệu bảng so sánh & Ma trận thông số
- **Endpoint:** `GET /compare`
- **Headers:** `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "success": true,
  "count": 2,
  "products": [...],
  "specs_matrix": {
    "keys": ["Layout", "Kết nối", "Switch", "Led"],
    "matrix": {
      "Kết nối": {
        "label": "Kết nối",
        "values": {
          "1": "Type-C / 2.4G / Bluetooth",
          "2": "Có dây Type-C"
        },
        "is_different": true
      }
    }
  },
  "diff_count": 1
}
```

### 2.2. Thêm sản phẩm vào so sánh
- **Endpoint:** `POST /compare/add/{id}`
- **Ràng buộc nghiệp vụ:**
  - Tối đa 4 sản phẩm.
  - Phải cùng Root Category (Không cho so sánh Chuột với Màn hình).
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Đã thêm 'Bàn phím cơ Aula F75' vào so sánh.",
  "count": 2
}
```
- **Response `422 Unprocessable Entity` (Khác danh mục cha):**
```json
{
  "success": false,
  "message": "Chỉ có thể so sánh các sản phẩm cùng nhóm danh mục ('Bàn phím cơ'). Vui lòng xóa bớt để chọn ngành hàng khác.",
  "count": 1
}
```

### 2.3. Xóa sản phẩm khỏi so sánh
- **Endpoint:** `DELETE /compare/remove/{id}`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Đã xóa sản phẩm khỏi danh sách so sánh.",
  "count": 1
}
```

---

## 3. PHÂN HỆ TƯ VẤN CHỌN GEAR THÔNG MINH (QUIZ FINDER)

### 3.1. Gửi câu trả lời trắc nghiệm & Nhận Top gợi ý
- **Endpoint:** `POST /quiz/submit`
- **Rate Limit:** Tối đa 15 lượt / phút.
- **Request Body (JSON):**
```json
{
  "purpose": "gaming",
  "budget": "1m_to_3m",
  "device_type": "ban-phim-co",
  "features": ["wireless", "rgb"]
}
```
- **Response `200 OK`:**
```json
{
  "success": true,
  "matches": [
    {
      "id": 1,
      "name": "Bàn phím cơ Aula F75",
      "price": 1250000,
      "sale_price": 990000,
      "match_score": 90,
      "match_percentage": 90,
      "match_reasons": [
        "Đúng danh mục thiết bị bạn cần",
        "Nằm trong khoảng ngân sách lý tưởng",
        "Tối ưu cho trải nghiệm Gaming",
        "Hỗ trợ kết nối không dây tiện lợi",
        "Đèn LED RGB rực rỡ cá tính"
      ]
    }
  ]
}
```

---

## 4. PHÂN HỆ GIỎ HÀNG & MÃ GIẢM GIÁ (CART & COUPONS)

### 4.1. Thêm sản phẩm vào giỏ hàng
- **Endpoint:** `POST /cart/add`
- **Request Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Đã thêm 2 sản phẩm 'Bàn phím cơ Aula F75' vào giỏ hàng!",
  "cartCount": 2,
  "subtotal": 1980000,
  "total": 1980000
}
```

### 4.2. Xem trước mã giảm giá (Preview Coupon)
- **Endpoint:** `POST /checkout/coupon`
- **Rate Limit:** 20 lượt / phút (Chống dò mã Brute-force).
- **Request Body:** `{ "code": "GEARHUB50K" }`
- **Response `200 OK`:**
```json
{
  "valid": true,
  "code": "GEARHUB50K",
  "discount": 50000,
  "discount_formatted": "50.000₫",
  "new_total": 1930000
}
```

---

## 5. PHÂN HỆ ĐÁNH GIÁ SẢN PHẨM (REVIEWS)

### 5.1. Lấy danh sách đánh giá & Phân bổ sao
- **Endpoint:** `GET /products/{product}/reviews`
- **Response `200 OK`:**
```json
{
  "success": true,
  "stats": {
    "average": 4.8,
    "total": 15,
    "breakdown": {
      "5": { "count": 12, "percentage": 80 },
      "4": { "count": 3, "percentage": 20 },
      "3": { "count": 0, "percentage": 0 },
      "2": { "count": 0, "percentage": 0 },
      "1": { "count": 0, "percentage": 0 }
    }
  },
  "eligibility": {
    "can_review": true,
    "reason": null
  },
  "reviews": [...]
}
```

### 5.2. Gửi đánh giá mới (Kèm ảnh chụp thật)
- **Endpoint:** `POST /products/{product}/reviews`
- **Auth:** Bắt buộc đăng nhập (`role:customer`).
- **Ràng buộc:** Chỉ khách hàng có đơn hàng trạng thái `completed` chứa sản phẩm đó mới được gửi.
- **Request (multipart/form-data):**
  - `rating`: 5
  - `comment`: "Bàn phím gõ rất đầm tay, switch mượt, giao hàng hỏa tốc!"
  - `images[]`: (File ảnh đính kèm, max 5MB).
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Cảm ơn bạn đã đánh giá sản phẩm!"
}
```

---

## 6. PHÂN HỆ DIỄN ĐÀN KHOE GÓC MÁY (COMMUNITY)

### 6.1. Danh sách bài viết khoe góc máy (Lưới Masonry)
- **Endpoint:** `GET /community`
- **Query Parameters:** `style` (`minimalist`, `rgb`, `white_aesthetic`, `dark_mode`, `vintage`).
- **Response `200 OK`:**
```json
{
  "success": true,
  "posts": {
    "data": [
      {
        "id": 1,
        "title": "Góc setup tối giản trắng xám cho Lập trình viên",
        "slug": "goc-setup-toi-gian-trang-xam-cho-lap-trinh-vien",
        "style": "minimalist",
        "image_url": "http://localhost:8000/storage/community/setup-1.webp",
        "likes_count": 142,
        "comments_count": 18,
        "products": [...]
      }
    ]
  }
}
```

### 6.2. Thả tim bài viết (Like Toggle)
- **Endpoint:** `POST /community/{id}/like`
- **Cơ chế chống spam:** Bọc trong Database Transaction với Row-level Lock (`lockForUpdate`).
- **Response `200 OK`:**
```json
{
  "success": true,
  "liked": true,
  "likes_count": 143
}
```

### 6.3. Đăng bình luận bài viết
- **Endpoint:** `POST /community/{id}/comments`
- **Auth:** Bắt buộc đăng nhập.
- **Request Body:** `{ "content": "Góc setup nhìn chill quá bạn ơi!" }`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Đã đăng bình luận thành công.",
  "comment": {
    "id": 5,
    "content": "Góc setup nhìn chill quá bạn ơi!",
    "user": { "name": "Nguyễn Văn A" }
  },
  "comments_count": 19
}
```

---

## 7. PHÂN HỆ ĐẶT HÀNG & THANH TOÁN (CHECKOUT & ORDERS)

### 7.1. Tạo đơn hàng (Chống bán âm kho & Deadlock)
- **Endpoint:** `POST /checkout`
- **Auth:** Bắt buộc đăng nhập.
- **Bảo vệ Concurrency:** Khóa bi quan `lockForUpdate()`, sắp xếp ID tăng dần (`orderBy('id')`) triệt tiêu Deadlock.
- **Request Body (JSON):**
```json
{
  "receiver_name": "Trần Hữu Thắng",
  "phone": "0987654321",
  "shipping_address": "Ký túc xá Khu B ĐHQG, Dĩ An, Bình Dương",
  "payment_method": "cod",
  "coupon_code": "GEARHUB50K",
  "note": "Giao giờ hành chính giúp em"
}
```
- **Response `200 OK`:** Chuyển hướng tới trang thành công `/checkout/success/{code}` kèm mã VietQR tự sinh động.

---

## 8. PHÂN HỆ QUẢN TRỊ VIÊN (ADMIN PORTAL APIS)

> **Lưu ý:** Tất cả các endpoint dưới đây đều được bảo vệ bởi middleware `['auth', 'role:admin']`. Nếu tài khoản không phải Admin, hệ thống trả về mã lỗi `403 Forbidden`.

- `GET /admin/dashboard`: Dữ liệu 4 thẻ KPI, Doanh thu 7 ngày, Cơ cấu doanh số danh mục (Chart.js), Top 5 bán chạy.
- `PATCH /admin/orders/{order}/status`: Chuyển trạng thái đơn (`pending` → `shipping` → `completed`).
- `POST /admin/trash/{product}/restore`: Khôi phục sản phẩm đã xóa mềm.
- `DELETE /admin/trash/{product}`: Xóa vĩnh viễn sản phẩm (Có kiểm tra bảo toàn khóa ngoại đơn hàng cũ).
- `PATCH /admin/users/{user}/toggle-status`: Khóa hoặc mở khóa tài khoản khách hàng.
- `PATCH /admin/community/{post}/status`: Duyệt bài đăng (`published`) hoặc ẩn bài vi phạm (`hidden`).