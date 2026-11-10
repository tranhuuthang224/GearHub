# KIẾN TRÚC HỆ THỐNG VÀ SƠ ĐỒ ĐỒ ÁN GEARHUB

---

## 1. SƠ ĐỒ CƠ SỞ DỮ LIỆU QUAN HỆ CHUẨN 3NF (ERD)

```mermaid
erDiagram
    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ REVIEWS : "writes"
    USERS ||--o{ COMMUNITY_POSTS : "publishes"
    USERS ||--o{ POST_COMMENTS : "comments"
    USERS ||--o{ POST_LIKES : "likes"
    USERS ||--o{ QUIZ_LOGS : "logs"

    CATEGORIES ||--o{ CATEGORIES : "parent_of"
    CATEGORIES ||--o{ PRODUCTS : "categorizes"
    BRANDS ||--o{ PRODUCTS : "manufactures"

    PRODUCTS ||--o{ PRODUCT_IMAGES : "has_many"
    PRODUCTS ||--o{ ORDER_ITEMS : "included_in"
    PRODUCTS ||--o{ REVIEWS : "receives"
    PRODUCTS ||--o{ POST_PRODUCT_TAGS : "tagged_in"

    ORDERS ||--o{ ORDER_ITEMS : "contains"
    ORDERS ||--o{ ORDER_STATUS_HISTORIES : "tracks"
    COUPONS ||--o{ ORDERS : "applied_to"

    COMMUNITY_POSTS ||--o{ POST_COMMENTS : "has"
    COMMUNITY_POSTS ||--o{ POST_LIKES : "receives"
    COMMUNITY_POSTS ||--o{ POST_PRODUCT_TAGS : "tags"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string phone
        string role "admin | customer"
        boolean is_active
        timestamps created_at
    }

    CATEGORIES {
        bigint id PK
        bigint parent_id FK
        string name
        string slug UK
        string image
    }

    BRANDS {
        bigint id PK
        string name
        string slug UK
        string logo
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        bigint brand_id FK
        string name
        string slug UK
        string sku UK
        json specs "JSON dynamic specs"
        bigint price
        bigint sale_price
        int stock_quantity
        string thumbnail
        boolean is_active
        timestamp deleted_at "Soft Deletes"
    }

    ORDERS {
        bigint id PK
        string code UK
        bigint user_id FK
        bigint coupon_id FK
        string receiver_name
        string phone
        text shipping_address
        string payment_method
        string status "pending | shipping | completed | cancelled"
        bigint subtotal
        bigint discount
        bigint total
    }

    REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        tinyint rating "1 - 5 stars"
        text comment
        json images
        boolean is_verified_purchase
    }

    COMMUNITY_POSTS {
        bigint id PK
        bigint user_id FK
        string title
        string slug UK
        text content
        string image
        string style "minimalist | rgb | white | dark"
        string status "published | hidden"
        int likes_count
        int comments_count
    }

    POST_PRODUCT_TAGS {
        bigint id PK
        bigint community_post_id FK
        bigint product_id FK
    }

    QUIZ_LOGS {
        bigint id PK
        bigint user_id FK
        string session_id
        json answers
        json recommended_product_ids
    }
```

---

## 2. CÁC SƠ ĐỒ TUẦN TỰ (SEQUENCE DIAGRAMS)

### 2.1. Luồng So sánh Sản phẩm & Ma trận Thông số (Compare & Specs Matrix)

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Khách hàng (User)
    participant Client as Browser (Alpine.js / Blade)
    participant CompareCtrl as CompareController
    participant CompareSvc as CompareService
    participant DB as Database (MySQL/SQLite)

    Customer->>Client: Bấm nút "So sánh" trên thẻ sản phẩm
    Client->>CompareCtrl: POST /compare/add/{id}
    CompareCtrl->>CompareSvc: add(productId)
    CompareSvc->>DB: Lấy thông tin & danh mục cha của sản phẩm
    
    alt Khác danh mục cha hoặc vượt quá 4 sản phẩm
        CompareSvc-->>CompareCtrl: Trả về lỗi 422 (Không cùng nhóm / Quá 4 món)
        CompareCtrl-->>Client: Hiển thị Toast thông báo lỗi
    else Hợp lệ
        CompareSvc->>CompareSvc: Lưu Product ID vào Session
        CompareSvc-->>CompareCtrl: Thêm thành công (Items & Count)
        CompareCtrl-->>Client: Cập nhật Sticky Compare Bar ở đáy màn hình
    end

    Customer->>Client: Bấm "So sánh ngay" (Chuyển đến /compare)
    Client->>CompareCtrl: GET /compare
    CompareCtrl->>CompareSvc: buildSpecsMatrix(products)
    CompareSvc->>CompareSvc: Đồng bộ hóa các key JSON (Điền '-' nếu thiếu, cờ is_different)
    CompareCtrl-->>Client: Render bảng Side-by-Side đa cột kèm highlight khác biệt
```

---

### 2.2. Luồng Smart Gear Finder Quiz & Match Engine

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Khách hàng (User)
    participant Client as UI Quiz Wizard (Alpine.js)
    participant QuizCtrl as QuizController
    participant MatchEngine as GearMatchEngine
    participant Log as QuizLog
    participant DB as Products DB

    Customer->>Client: Hoàn thành 4 bước (Nhu cầu -> Ngân sách -> Thiết bị -> Tính năng)
    Client->>QuizCtrl: POST /quiz/submit (payload JSON câu trả lời)
    QuizCtrl->>MatchEngine: forAnswers(payload)->getTopMatches(3)
    MatchEngine->>DB: Truy vấn sản phẩm khả dụng trong kho
    
    loop Chấm điểm trọng số cho từng sản phẩm
        MatchEngine->>MatchEngine: Khớp thiết bị/danh mục: +40 điểm
        MatchEngine->>MatchEngine: Khớp khoảng ngân sách: +30 điểm
        MatchEngine->>MatchEngine: Khớp tính năng ưu tiên (Wireless, RGB...): +10 điểm/mục
        MatchEngine->>MatchEngine: Tính % độ phù hợp & trích xuất lý do gợi ý
    end

    MatchEngine-->>QuizCtrl: Trả về Top 3 sản phẩm có điểm cao nhất
    QuizCtrl->>Log: Lưu vết câu trả lời & ID gợi ý (phân tích thị hiếu)
    QuizCtrl-->>Client: Trả về JSON Top 3 sản phẩm kèm % Match & lý do
    Client-->>Customer: Hiển thị màn hình kết quả kèm huy hiệu % và nút Mua ngay
```

---

### 2.3. Luồng Diễn đàn Social Commerce Khoe góc máy (Community & Tagging)

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Khách hàng (User)
    participant Client as Community UI
    participant CommCtrl as CommunityController
    participant CommSvc as CommunityService
    participant DB as Database

    Customer->>Client: Điền form tạo bài viết, chọn ảnh setup & gắn tag gear
    Client->>CommCtrl: POST /community (multipart/form-data)
    CommCtrl->>CommSvc: createPost(user, data, image, productIds)
    CommSvc->>CommSvc: Upload ảnh vào storage/community & tạo slug
    CommSvc->>DB: Lưu CommunityPost & sync vào bảng pivot post_product_tags
    CommCtrl-->>Client: Chuyển hướng tới /community/{slug}

    Customer->>Client: Bấm nút "Thả tim" (Like)
    Client->>CommCtrl: POST /community/{id}/like
    CommCtrl->>DB: DB::transaction (lockForUpdate kiểm tra PostLike)
    alt Đã like trước đó
        CommCtrl->>DB: Xóa PostLike, giảm likes_count
    else Chưa like
        CommCtrl->>DB: Tạo PostLike, tăng likes_count
    end
    CommCtrl-->>Client: Trả về trạng thái liked & likes_count mới nhất (chống spam)
```