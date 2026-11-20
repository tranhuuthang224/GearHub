# GEARHUB DEFENSE PRESENTATION & LIVE DEMO SCRIPT
## Next-Gen Gaming Gear & Tech Ecosystem E-Commerce Platform

> **Institution:** University of Information Technology – Vietnam National University, Ho Chi Minh City (UIT - VNU-HCM)  
> **Course:** Web Application Development / Modern Web Technologies  
> **Project Title:** **GearHub**  
> **Live Production URL:** [https://www.gearhub.io.vn](https://www.gearhub.io.vn)  
> **Interactive API Docs:** [https://www.gearhub.io.vn/docs/api](https://www.gearhub.io.vn/docs/api)  
> **Repository:** [https://github.com/tranhuuthang224/GearHub](https://github.com/tranhuuthang224/GearHub)  
> **Automated Test Suite:** **275 / 275 Passed (100%)** | 1100+ Assertions  
> **Total Target Time:** **15 – 20 Minutes** (8 mins Presentation + 7 mins Live Demonstration + 5 mins Q&A)  

---

## PRESENTATION STRUCTURE & TIMELINE OVERVIEW

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       GEARHUB DEFENSE TIMELINE (15 - 20 MINS)               │
├────────────────────────────────┬────────────────────────────────────────────┤
│ PART 1: THEORETICAL & DESIGN   │ • Slides 1 - 9 (~8 minutes)                │
│ • Problem, Taxonomy & Tech     │ • Architecture, Concurrency, Algorithms,   │
│ • Differentiators & Highlights │   VietQR, Community, Database Schema       │
├────────────────────────────────┼────────────────────────────────────────────┤
│ PART 2: LIVE APPLICATION DEMO  │ • Slides 10 - 14 (~7 minutes)              │
│ • Interactive Walkthrough      │ • Discovery → Smart Quiz → Dual Comparator │
│ • Live Click-by-Click Guide    │ • Concurrency Checkout → VietQR → Community│
├────────────────────────────────┼────────────────────────────────────────────┤
│ PART 3: QA, METRICS & DEFENSE  │ • Slides 15 - 18 (~5 minutes)              │
│ • Testing & Critical Review    │ • 275 Tests, Web Vitals, Edge Proxying     │
│ • Q&A Cheatsheet Responses     │ • Committee Inquiries & Conclusion         │
└────────────────────────────────┴────────────────────────────────────────────┘
```

---

# PART 1: PRESENTATION SLIDES & SPOKEN SCRIPT

---

### SLIDE 1: TITLE & PROJECT COVER
* **Visual Elements on Slide:**
  * GearHub Logo (Cyber-Red `#FF0000` & Deep Navy `#0F172A`).
  * Subtitle: *"A High-Performance Web Application with Algorithmic Recommendation & Concurrency-Safe Transaction Engine"*.
  * Live Production Badge: `https://www.gearhub.io.vn` (SSL Active).
  * Author & Advisor details: Student Name, Student ID, Class, Supervisor.

* **Spoken Script (English):**
  > *"Good morning, respected members of the Academic Evaluation Board and fellow students. My name is **Tran Huu Thang**, and today I am honored to present our final capstone project: **GearHub – A Next-Generation Gaming Gear and Tech Ecosystem E-Commerce Platform**.*
  > 
  > *GearHub is not merely another basic CRUD online store. It is a production-grade, domain-specialized web platform engineered specifically to tackle the high informational asymmetry in peripheral hardware, eradicate transaction race conditions during high-demand flash sales, and merge social validation directly into e-commerce conversion. Our application is currently deployed and running live at **gearhub.io.vn**."*

---

### SLIDE 2: PROBLEM FORMULATION & MARKET LANDSCAPE
* **Visual Elements on Slide:**
  * Diagram of the 3 Architectural Archetypes:
    1. *Class A (Generalist Marketplaces: Shopee/Lazada):* High scale, zero domain curation, severe choice paralysis, counterfeit noise.
    2. *Class B (Traditional IT Retail Portals: Phong Vu/MemoryZone):* ERP-centric, static filters, zero biometric recommendation.
    3. *Class C (Niche Artisan Group-Buys: Drop):* Segmented UX, zero domestic payment integration.
  * The Central Bottleneck Callout: **The Decision Fatigue & Concurrency Dilemma**.

* **Spoken Script (English):**
  > *"To understand why we built GearHub, let us look at the current market landscape. In the multi-billion-dollar gaming peripheral industry, computer mice, mechanical keyboards, and studio displays are no longer treated as generic office commodities—they are precision ergonomic instruments. A variance of 2 millimeters in grip width or a slight change in switch actuation force directly impacts user fatigue and competitive performance.*
  > 
  > *However, contemporary horizontal marketplaces like Shopee or Lazada treat gaming peripherals as generic alphanumeric SKUs, overwhelming consumers with unstructured listings and choice paralysis. Conversely, traditional tech retailers rely on rigid, static filters with no decision assistance. Furthermore, during high-demand promotional drops, conventional web applications frequently suffer from overselling and database lockups.*
  > 
  > *GearHub bridges this exact gap: combining domain-curated hardware taxonomy, algorithmic recommendation, concurrency-safe transactional integrity, and domestic VietQR payment."*

---

### SLIDE 3: MULTI-TIER SYSTEM ARCHITECTURE & TECH STACK
* **Visual Elements on Slide:**
  * Layered Architecture Diagram:
    * **Client Layer:** Blade Components, Tailwind CSS, Alpine.js (15KB Reactive state).
    * **Application Layer:** Laravel 11.x, PHP 8.3 JIT, Service-Layer Pattern.
    * **Data Layer:** MySQL 8 InnoDB, Row-Level Locking, JSON Columns, 3NF Normalized.
    * **Infrastructure Layer:** Railway PaaS, FrankenPHP Containerization, Cloudflare SSL Edge Routing.

* **Spoken Script (English):**
  > *"Moving to our system architecture. GearHub is designed under a decoupled, multi-tier architectural paradigm.*
  > 
  > *On the frontend, instead of adopting heavy, state-fragile Single Page Application frameworks that degrade initial First Contentful Paint, we implemented a server-rendered **Blade Component Library** paired with **Tailwind CSS** and **Alpine.js**. Alpine delivers reactive micro-interactions—such as dynamic modals, toast alerts, and live cart synchronization—with an overhead of only 15 kilobytes.*
  > 
  > *On the backend, we leverage **PHP 8.3** running on **Laravel 11**, structured with a strict **Service Layer Pattern**. Heavy domain logic is decoupled from HTTP controllers into dedicated services.*
  > 
  > *Our production environment runs in a containerized **FrankenPHP** worker on **Railway Cloud**, routed through an edge reverse-proxy with automated TLS/SSL encryption and strict Content Security Policies."*

---

### SLIDE 4: DATABASE SCHEMA & 3NF NORMALIZATION
* **Visual Elements on Slide:**
  * Entity Relationship Diagram (Mermaid ERD) showing the 12 core tables:
    * `users`, `categories` (Self-referencing tree), `brands`, `products`, `orders`, `order_items`, `coupons`, `community_posts`, `user_addresses`, `order_status_histories`.
  * Highlight Boxes:
    * **JSON Specs Column:** Flexible schema for heterogeneous hardware attributes without EAV join penalty.
    * **Price Snapshotting:** `unit_price` frozen in `order_items`.
    * **Soft Deletes:** `deleted_at` audit compliance.

* **Spoken Script (English):**
  > *"Our database architecture is designed in strict Third Normal Form (3NF) comprising 12 interconnected tables.*
  > 
  > *I would like to highlight three critical design decisions in our schema:*
  > *First, **Heterogeneous Specification Storage**: Mice have DPI and sensors; mechanical keyboards have switch types and gasket mounts; monitors have refresh rates and panel types. Implementing traditional Entity-Attribute-Value (EAV) tables would require extensive table joins, killing query performance. Instead, we utilize MySQL’s native **JSON `specs` column** with automated Eloquent array casting, preserving relational integrity while allowing arbitrary technical attributes.*
  > *Second, **Price Snapshotting**: In our `order_items` table, we store the frozen `product_name` and `unit_price` at the exact millisecond of checkout. Even if an administrator alters pricing or discounts later, historical financial records remain immutable.*
  > *Third, **Soft Deletes**: Products utilize Laravel's `SoftDeletes` with `Product::withTrashed()` queries, ensuring order histories and stock restock loops never fail due to missing relational foreign keys."*

---

### SLIDE 5: ENGINEERING HIGHLIGHT 1 – CONCURRENCY-SAFE INVENTORY & DEADLOCK ELIMINATION
* **Visual Elements on Slide:**
  * Side-by-Side Code Comparison:
    * *Naive Code (Vulnerable):* Read stock $\rightarrow$ Check $\rightarrow$ Decrement $\rightarrow$ Overselling!
    * *GearHub Defensive Code:* `Product::orderBy('id')->lockForUpdate()` + Atomic conditional update + `DB::transaction(..., 3)`.
  * The Two-Tier Deadlock Defense Diagram: **Resource Ordering** (Always lock lowest ID first) + **Automatic Deadlock Retry (Error 1213)**.

* **Spoken Script (English):**
  > *"Now, let us examine our most critical engineering achievement: **High-Concurrency Inventory Management and Deadlock Elimination**.*
  > 
  > *Consider a flash-sale scenario: the last Aula F75 keyboard is in stock. Two customers hit 'Confirm Order' at the exact same millisecond. In a naive implementation, both processes read `stock = 1`, both approve the order, and the inventory drops to negative one—a disastrous race condition.*
  > 
  > *In GearHub's `OrderService`, we enforce a dual-layer defense:
  > First, **Pessimistic Row-Level Locking**: we execute `SELECT ... FOR UPDATE` inside a database transaction, forcing subsequent concurrent transactions into a queue until the first transaction commits or rolls back.*
  > *Second, we eliminate **Deadlocks**: if Customer 1 buys Item A and B, while Customer 2 buys Item B and A, circular wait occurs. We neutralize this by **mandating Resource Ordering**: all product IDs are explicitly sorted in ascending order (`orderBy('id')`) before acquiring the lock. Every transaction acquires locks in the exact same sequence, mathematically eliminating circular dependency.*
  > *Finally, as a fail-safe against internal InnoDB gap-lock collisions, transactions are wrapped in `DB::transaction(..., 3)` to automatically retry up to three times upon receiving MySQL Error 1213."*

---

### SLIDE 6: ENGINEERING HIGHLIGHT 2 – ALGORITHMIC RECOMMENDATION ENGINE (GEARMATCH)
* **Visual Elements on Slide:**
  * 4-Question Interactive Flow: Device Category $\rightarrow$ Primary Use-Case $\rightarrow$ Budget Tier $\rightarrow$ Priority Feature.
  * Scoring Formula Breakdown:
    $$\text{Total Score} = S_{\text{Category}} (40\text{ pts}) + S_{\text{Budget}} (30\text{ pts}) + \sum S_{\text{Feature}} (10\text{ pts/match})$$
  * Output: Top 3 Personalized Matches + Match Percentage Badge + Dynamic Justification Bullet Points.

* **Spoken Script (English):**
  > *"Our second differentiator is the **Smart Gear Finder Quiz**, powered by the `GearMatchEngine` service.*
  > 
  > *Rather than relying on ungrounded external generative AI APIs that introduce non-deterministic hallucinations, latency, and recurring token costs, we architected a **deterministic rule-based multi-vector scoring algorithm**.*
  > 
  > *Users complete a 30-second assessment capturing four vectors: hardware category, primary ergonomic use case, budget threshold, and specific feature priorities such as wireless connectivity, ultra-lightweight chassis, or high refresh rate.*
  > 
  > *The engine evaluates candidate gear against weighted criteria: 40 points for category alignment, 30 points for strict budget boundaries, and 10 points for each matching specification keyword. It standardizes scores into a percentage, ranks candidates, and returns the top 3 best-fitting peripherals accompanied by human-readable justification reasons within 50 milliseconds."*

---

### SLIDE 7: ENGINEERING HIGHLIGHT 3 – DUAL-MODE HARDWARE SPECIFICATION COMPARATOR
* **Visual Elements on Slide:**
  * Screenshot of the Side-by-Side Compare Table (`/compare`).
  * Annotated Features:
    * **Sticky Header:** Product thumbnail, title, price locked on vertical scroll.
    * **Sticky Left Column:** Specification categories locked on horizontal pan.
    * **Difference Highlighting:** `⚡ Highlight Differences` toggle (Yellow row emphasis).
    * **1-Click Presets:** Monitors (OLED vs Fast IPS), Keyboards (Custom Aula vs Akko), Mice (Ultralight FPS).

* **Spoken Script (English):**
  > *"Our third core feature is the **Dual-Mode Technical Specification Comparator**.*
  > 
  > *Evaluating intricate gaming hardware requires comparing up to four items side-by-side. Our frontend employs CSS sticky positioning on both axes: a sticky header for product titles and prices during vertical scrolling, and a sticky left column for technical labels during horizontal overflow on mobile devices.*
  > 
  > *On the backend, `CompareService` dynamically merges the heterogeneous JSON specification attributes from all selected models into a unified matrix. It executes in-memory string and numeric delta evaluations, allowing users to toggle the **'Highlight Differences'** mode, which instantly highlights divergence rows in yellow.*
  > 
  > *Furthermore, to eliminate empty-state friction, we engineered **1-click Quick Compare Presets** covering our top benchmark battles: OLED versus Fast IPS monitors, custom gasket versus factory mechanical keyboards, and flagship wireless esports mice."*

---

### SLIDE 8: ENGINEERING HIGHLIGHT 4 – COMMUNITY SETUP SHOWCASE & SOCIAL COMMERCE
* **Visual Elements on Slide:**
  * Pinterest-Style Masonry Grid screenshot (`/community`).
  * Detailed Callouts:
    * Multi-category Gear Tagging (Mice, Keyboards, Headsets, Displays, Laptops).
    * Unaccented Vietnamese Search (`chuot`, `ban phim`, `tai nghe`).
    * Full Lifecycle Governance: Real-time likes, threaded comments, and author-governed deletion with storage unlink.

* **Spoken Script (English):**
  > *"To drive organic user acquisition and real-world conversion, we built the **Community Battlestation Showcase**.*
  > 
  > *Users share high-resolution photographs of their workstation or gaming setups. Crucially, creators can **tag exact GearHub products** directly into their setup post. Readers browsing community showcases can click any tagged gear chip to transition immediately to the product page and checkout, establishing a direct Social Commerce conversion loop.*
  > 
  > *In our latest build, we upgraded the tagging subsystem with **Quick Category Filter Pills** (Mice, Keyboards, Headsets, Monitors, Laptops) and **accent-insensitive search normalization**—allowing users to search in unaccented Vietnamese like 'chuot' or 'ban phim' as well as English synonyms like 'mouse' or 'keyboard'.*
  > 
  > *Authors and administrators are empowered with complete lifecycle control, including direct post deletion with automated disk asset cleanup."*

---

### SLIDE 9: ENGINEERING HIGHLIGHT 5 – DYNAMIC VIETQR PAYMENT & ORDER LIFECYCLE
* **Visual Elements on Slide:**
  * Dynamic VietQR Display on Checkout Success (`/checkout/success/{code}`).
  * State Machine Diagram:
    $$\text{Pending} \xrightarrow{\text{Admin Ship}} \text{Shipping} \xrightarrow{\text{Delivered}} \text{Completed}$$
    $$\text{Pending} \xrightarrow{\text{Customer/Admin Cancel}} \text{Cancelled (Triggers Restock \& Coupon Release)}$$
  * Callouts: Napas247 VietinBank format, exact amount encoding, `GEARHUB {CODE}` transaction memo, 1-click clipboard copy.

* **Spoken Script (English):**
  > *"Our final technical highlight is our domestic payment integration: **Real-Time Dynamic VietQR Generation**.*
  > 
  > *Rather than requiring fragile external third-party payment gateway redirects that charge hefty merchant processing fees, GearHub integrates native **Napas247 VietQR generation** via `VietQrService`.*
  > 
  > *Upon checkout, the system generates an encrypted, standardized banking QR code for VietinBank with the exact payable amount and an immutable transfer memo formatted as `GEARHUB {ORDER_CODE}`. Customers can scan via any mobile banking app in Vietnam with zero manual typing errors.*
  > 
  > *Our order lifecycle is governed by an automated state machine. When an order is cancelled, the system executes an automated restock transaction, restoring inventory counts to the database, releasing coupon utilization limits, and recording an immutable audit trail in `order_status_histories`."*

---

# PART 2: LIVE DEMO PROTOCOL & SCRIPT
*(Estimated Time: 6 - 8 Minutes | Browser window open at `https://www.gearhub.io.vn`)*

---

### SLIDE 10: LIVE DEMO OVERVIEW & PRE-FLIGHT
* **Visual Elements on Slide:**
  * Live Demo Roadmap:
    1. **Step 1:** Discovery & Faceted Filtering (`/shop`)
    2. **Step 2:** Algorithmic Smart Quiz (`/quiz`)
    3. **Step 3:** Side-by-Side Dual Comparator (`/compare`)
    4. **Step 4:** Cart, Concurrency-Safe Checkout & VietQR (`/checkout`)
    5. **Step 5:** Community Showcase, Gear Tagging & Deletion (`/community`)
    6. **Step 6:** Admin Panel, Low-Stock Warnings & Soft Delete Trash (`/admin`)

* **Spoken Script (English):**
  > *"We will now transition to the live system demonstration on our production deployment at **gearhub.io.vn**. I will walk the committee through the complete user journey: from intelligent hardware discovery and comparison, through concurrency-safe checkout and VietQR payment, to community interaction and administrative governance."*

---

### SLIDE 11: [LIVE DEMO STEP 1 & 2] DISCOVERY, SMART QUIZ & COMPARATOR
* **Demo Action & Pointer Guide:**
  1. **Open Browser:** Navigate to `https://www.gearhub.io.vn`.
     * *Show:* The clean, modern header, responsive Hero Banner Slider with `fetchpriority="high"`, and Best Seller cards.
  2. **Navigate to Shop (`/shop`):**
     * *Action:* Click on the **"Màn hình"** (Monitors) category.
     * *Show to Committee:* Notice that selecting the parent category automatically and recursively queries all subcategories (Gaming, Graphic Design, Office monitors).
     * *Action:* Check the **"In Stock"** filter and drag the price slider. Point out URL query parameters syncing cleanly.
  3. **Launch Smart Quiz (`/quiz`):**
     * *Action:* Click **"Tư vấn chọn Gear (QUIZ)"** on the navigation bar.
     * *Step-by-Step clicks:*
       - Q1: Select **"Bàn phím cơ"** (Mechanical Keyboard).
       - Q2: Select **"Lập trình & gõ văn bản"** (Coding / Typing).
       - Q3: Select **"Từ 1 đến 3 triệu"** (1M - 3M VND).
       - Q4: Select **"Không dây (Wireless)"**.
     * *Action:* Click **"Xem kết quả tư vấn"**.
     * *Show to Committee:* Highlight the immediate sub-50ms response. Point to the **Top 3 results**, the **Match Percentage badge (e.g., 95% Phù hợp)**, and the exact bullet-pointed technical justification reasons generated by `GearMatchEngine`.
  4. **Launch Dual Comparator (`/compare`):**
     * *Action:* Click the scale icon (⚖) on the recommended Aula F75 keyboard $\rightarrow$ Observe the **Sticky Compare Bar** floating smoothly at the bottom.
     * *Action:* Click on `/compare`.
     * *Show to Committee:* Click the **"⚡ Làm nổi bật khác biệt"** toggle switch $\rightarrow$ show how rows with differing switch types, battery capacities, and layout percentages instantly highlight in yellow. Point out the sticky header and left specification column.

* **Spoken Script during Demo (English):**
  > *"As you can see on the screen, our store catalog implements recursive category inheritance. Selecting 'Monitors' automatically queries all child graphic and gaming displays.*
  > 
  > *Now, let us test the Smart Gear Finder Quiz. I select Mechanical Keyboard, Coding use case, 1 to 3 million VND budget, and Wireless capability. Submitting the quiz triggers our `GearMatchEngine` in under 50 milliseconds, returning the Aula F75 with a 95% match score, accompanied by explicit technical justifications.*
  > 
  > *Adding it to our comparison matrix, observe our Dual-Mode comparator. Toggling 'Highlight Differences' immediately isolates diverging technical attributes, allowing buyers to make definitive hardware decisions without confusion."*

---

### SLIDE 12: [LIVE DEMO STEP 3 & 4] CHECKOUT, CONCURRENCY SAFETY & DYNAMIC VIETQR
* **Demo Action & Pointer Guide:**
  1. **Add to Cart (`/cart`):**
     * *Action:* From the product detail page, click **"Thêm vào giỏ hàng"** (Add to Cart).
     * *Show to Committee:* The cart badge in the header instantly updates via custom Alpine event `cart-toast` without a full page reload; a clean toast notification appears in the top-right corner.
     * *Action:* Go to `/cart`, try increasing the quantity beyond available stock $\rightarrow$ show the friendly warning capping at max available quantity.
     * *Action:* Enter coupon code `GEARHUB100K` $\rightarrow$ click Apply $\rightarrow$ observe the 100,000 VND discount deducted from the subtotal.
  2. **Proceed to Checkout (`/checkout`):**
     * *Action:* Click **"Tiến hành đặt hàng"**.
     * *Show to Committee:* Pre-filled customer address from the address book (`is_default`), radio toggle between COD and **Chuyển khoản VietQR**.
     * *Action:* Select **"Chuyển khoản VietQR 24/7"** $\rightarrow$ Click **"Xác nhận đặt hàng"**.
  3. **Checkout Success (`/checkout/success/{code}`):**
     * *Show to Committee:* Point to the dynamically generated **VietinBank QR Code**.
     * *Highlight:* Point out that the payable total is exact to the single Dong, and the memo is pre-configured with the exact order code.
     * *Action:* Click the **"Sao chép STK"** (Copy Account Number) and **"Sao chép nội dung"** buttons $\rightarrow$ show the instant clipboard confirmation toast.

* **Spoken Script during Demo (English):**
  > *"Now, let us proceed through our transaction pipeline. Adding an item to the cart updates our reactive Alpine header state instantaneously.*
  > 
  > *At checkout, our `OrderService` executes within a database transaction. When I submit the order, the system locks the target product rows with `SELECT ... FOR UPDATE`, re-verifies inventory, and commits atomically.*
  > 
  > *On the confirmation screen, observe our dynamic VietQR implementation: the VietinBank QR image is rendered with the exact payable amount and immutable order code. A customer simply opens their banking app, scans, and pays with zero manual entry friction."*

---

### SLIDE 13: [LIVE DEMO STEP 5] COMMUNITY SHOWCASE, GEAR TAGGING & AUTHOR GOVERNANCE
* **Demo Action & Pointer Guide:**
  1. **Navigate to Community (`/community`):**
     * *Show to Committee:* The Pinterest-style Masonry layout with custom setup photography.
     * *Action:* Click the style filters: *Minimalist, RGB, Wood Warm, Esports* $\rightarrow$ observe instant filtering.
     * *Action:* Click on an existing post $\rightarrow$ open the **Pinterest Modal** $\rightarrow$ show the high-res image on the left, author info, like button, and the **Tagged Gear list** on the right with direct links to buy.
  2. **Create Post (`/community/create`):**
     * *Action:* Click **"Đăng bài khoe góc máy"**.
     * *Show to Committee:* Drag and drop a setup photo into the dropzone.
     * *Highlight the New Feature:* Point to the **Category Filter Pills**: `Tất cả`, `🖱️ Chuột`, `⌨️ Bàn phím`, `🎧 Tai nghe`, `🖥️ Màn hình`, `💻 Laptop`.
     * *Action:* Click **`🖱️ Chuột`** $\rightarrow$ show that 15 gaming mice appear instantly without typing.
     * *Action:* Type `ban phim` (unaccented Vietnamese) into the search bar $\rightarrow$ show mechanical keyboards appearing immediately. Click **`+ Gắn thẻ`**.
     * *Action:* Click **"Đăng góc máy ngay"** $\rightarrow$ post publishes and opens.
  3. **Delete Own Post Feature:**
     * *Action:* Point out the red **"Xóa bài"** (Delete Post) button visible to the author on `/community/{slug}` and inside the modal.
     * *Action:* Click **"Xóa bài"** $\rightarrow$ browser displays confirmation alert $\rightarrow$ click Confirm $\rightarrow$ post is deleted cleanly from CSDL, photo file is unlinked from disk, redirects to `/community`.

* **Spoken Script during Demo (English):**
  > *"Next, we demonstrate our Social Commerce engine: the Community Showcase. Users share battlestations rendered in an adaptive masonry grid. Clicking any setup reveals the exact gear used, enabling direct conversion.*
  > 
  > *When authoring a post, notice our newly upgraded tagging system. Rather than typing complex names, users click our Quick Category Pills—instantly browsing mice, keyboards, or headsets. Our search engine also handles unaccented Vietnamese, matching 'ban phim' or 'chuot' seamlessly.*
  > 
  > *Furthermore, authors and admins possess full lifecycle control: clicking 'Delete Post' prompts confirmation, purges the database record, and unlinks uploaded image files from the physical storage disk to maintain server hygiene."*

---

### SLIDE 14: [LIVE DEMO STEP 6] ADMIN CONTROL PANEL & GOVERNANCE
* **Demo Action & Pointer Guide:**
  1. **Navigate to Admin Dashboard (`/admin/dashboard`):**
     * *Show to Committee:* The executive overview with revenue metrics, completed order counters, and the **"Cảnh báo tồn kho thấp"** (Low Stock Alert) table highlighting products with $\le 5$ units in stock.
  2. **Product Management & Soft Delete Trash (`/admin/products` & `/admin/trash`):**
     * *Action:* Locate a test product $\rightarrow$ click delete $\rightarrow$ show it disappears from the live shop.
     * *Action:* Navigate to `/admin/trash` $\rightarrow$ show the item residing in the Soft Delete repository.
     * *Action:* Click **"Khôi phục" (Restore)** $\rightarrow$ product is instantly restored to the catalog.
  3. **Order State Transition (`/admin/orders`):**
     * *Action:* View the recent VietQR order $\rightarrow$ transition status from *Pending* to *Shipping* $\rightarrow$ show audit log updated in `order_status_histories`.

* **Spoken Script during Demo (English):**
  > *"Finally, we enter the Administrative Control Panel at `/admin/dashboard`. Administrators have immediate visibility into total revenue and critical low-stock alerts for items with five or fewer units remaining.*
  > 
  > *Our catalog management implements enterprise-grade Soft Deletes. Trashing a product removes it from public storefront queries while preserving historical relational integrity. In `/admin/trash`, administrators can restore items with one click or execute a permanent purge.*
  > 
  > *Order state transitions automatically update immutable audit logs, completing our governance loop."*

---

# PART 3: QUALITY ASSURANCE, DEFENSE Q&A & CONCLUSION

---

### SLIDE 15: QUALITY ASSURANCE & TESTING SUITE METRICS
* **Visual Elements on Slide:**
  * Terminal screenshot showing PHPUnit green pass:
    `Tests: 275 passed (1100+ assertions), Duration: ~18s, 0 failures`.
  * Breakdown of Test Coverage:
    * **Feature Tests:** Auth, Cart, Checkout, VietQR, Quiz, Compare, Community, Admin.
    * **Unit Tests:** `GearMatchEngine`, `OrderService`, `CartService`.
    * **Concurrency & Race Condition Tests:** `orders:race-test` multi-process CLI simulation.
  * Performance Optimizations Table: Lazy loading, `fetchpriority="high"`, HTTP/2, CSP headers.

* **Spoken Script (English):**
  > *"A web application is only as good as its verified reliability. GearHub is backed by a rigorous, test-driven automated suite comprising **275 PHPUnit test cases** with over **1,100 assertions**, achieving a **100% pass rate with zero regressions**.*
  > 
  > *Our test suite validates every edge case: unaccented search normalization, coupon boundary calculations, unauthorized admin intrusion attempts returning HTTP 403, and multi-process concurrency race conditions.*
  > 
  > *On the performance front, we enforce strict Core Web Vitals optimizations: prioritizing Largest Contentful Paint images with `fetchpriority='high'`, deferring offscreen assets with native lazy loading, and eliminating mixed-content vulnerabilities with automated CSP upgrade directives."*

---

### SLIDE 16: CRITICAL EVALUATION & EMPIRICAL COMPARISON
* **Visual Elements on Slide:**
  * Empirical Comparison Table:

| Evaluation Metric | Generalist Marketplaces (Shopee) | Traditional IT Portals (Phong Vu) | **GearHub Platform** |
| :--- | :---: | :---: | :---: |
| **Domain Curation** | Low (Generic SKUs) | Moderate (Static Filters) | **High (Specialized Taxonomy)** |
| **Ergonomic Decision Support** | None | None | **Algorithmic Quiz (GearMatch)** |
| **Hardware Comparator** | None / Clunky | Basic Relational Specs | **Dynamic JSON Specs Matrix** |
| **Concurrency Overselling** | Occasional Out-of-Stock | Manual Sync Delay | **Pessimistic Lock (Zero Race)** |
| **Social Commerce** | Separate Reviews | None | **Integrated Setup Tagging** |
| **Payment Friction** | External Gateway Redirection | Manual Bank Transfer | **Dynamic Native VietQR 24/7** |

* **Spoken Script (English):**
  > *"In our empirical evaluation against existing platforms, GearHub demonstrates clear structural advantages. While generalist marketplaces offer scale at the cost of curation and traditional retailers lack intelligent decision engines, GearHub delivers domain-specialized discovery, sub-50ms recommendation matching, zero-overselling transactional guarantees, and integrated community proof."*

---

### SLIDE 17: LESSONS LEARNED & FUTURE ROADMAP
* **Visual Elements on Slide:**
  * Timeline diagram showing Roadmap Milestones:
    * *Phase 1 (Completed):* Core E-Commerce, Concurrency Safety, VietQR, Community Showcase, Quiz.
    * *Phase 2 (Next 6 Months):* Automated Banking Webhook (OpenBanking API) for real-time payment status reconciliation without manual admin verification.
    * *Phase 3 (Next 12 Months):* Multi-Vendor Marketplace integration allowing verified artisan keyboard switch and mouse cable makers to open storefronts.
    * *Phase 4:* On-device Local LLM assistant fine-tuned on switch acoustic sound profiles.

* **Spoken Script (English):**
  > *"Reflecting on our engineering journey, we gained deep mastery in relational concurrency control, transaction isolation levels, and modern defensive web architecture.*
  > 
  > *Looking forward, our immediate roadmap includes integrating OpenBanking real-time webhooks for zero-touch bank reconciliation, followed by multi-vendor marketplace onboarding for artisan peripheral creators."*

---

### SLIDE 18: CONCLUSION, LIVE ACCESS & Q&A
* **Visual Elements on Slide:**
  * Live QR Code linking to `https://www.gearhub.io.vn`.
  * Links to Repository: `github.com/tranhuuthang224/GearHub`.
  * Links to API Docs: `gearhub.io.vn/docs/api`.
  * Large Text: **"THANK YOU FOR YOUR TIME & ATTENTION!"**
  * Subtext: *"We welcome all questions and inquiries from the Academic Committee."*

* **Spoken Script (English):**
  > *"To conclude, GearHub successfully demonstrates how modern web engineering principles, rigorous database concurrency control, and domain-specialized user experience design can transform an ordinary digital shop into a thriving, high-conversion commercial ecosystem.*
  > 
  > *The entire system is live and accessible right now at **gearhub.io.vn**.*
  > 
  > *I would like to express my sincere gratitude to our supervisor and the distinguished members of the Academic Evaluation Committee for your time, guidance, and evaluation. I am now ready and eager to answer your questions. Thank you very much!"*

---

# PART 4: DEFENSE Q&A RAPID CHEATSHEET
*(Keep this on your secondary monitor or notes sheet during the Q&A session)*

### Q1: "Why did you use Pessimistic Locking instead of Optimistic Locking for order placement?"
* **Your Answer (English):**
  > *"Professor, Optimistic Locking using a version column works well when write conflicts are rare, such as editing user profiles. However, in flash-sale e-commerce where hundreds of gamers compete for limited stock, optimistic locking causes 99% of concurrent requests to fail with conflict errors or causes a massive 'retry storm' that overburdens the database server. With **Pessimistic Locking (`lockForUpdate`)**, concurrent buyers are queued cleanly at the database level. The first buyer acquires the lock, decrements the inventory, and commits; subsequent buyers immediately read the updated zero stock and receive an accurate out-of-stock notification with zero wasted retries."*

### Q2: "Can Deadlocks still occur under heavy load? How did your team solve it?"
* **Your Answer (English):**
  > *"Yes, Professor. Deadlocks occur when concurrent transactions request locks in circular order—for example, Transaction 1 locks Item A and requests B, while Transaction 2 locks Item B and requests A. We eradicated this by enforcing **Resource Ordering**: in `OrderService`, we sort all product IDs in ascending order (`orderBy('id')`) before acquiring the lock. Both transactions must acquire locks in the exact same sequence (lowest ID first), mathematically breaking the cyclic wait condition. Furthermore, as an extra defense, all critical transactions are wrapped in `DB::transaction(..., 3)` to automatically recover from any internal InnoDB gap-lock collisions."*

### Q3: "How do you handle the heterogeneous specifications of different gear categories without slowing down SQL queries?"
* **Your Answer (English):**
  > *"Traditional EAV (Entity-Attribute-Value) models require multiple SQL table joins, which degrades performance under heavy traffic. We utilized MySQL 8's native **JSON column (`specs`)** with Laravel array casting. In our `/compare` route, `CompareService` dynamically merges the JSON keys in memory across the selected models, standardizes the attributes, and evaluates differences on the fly. This gives us complete schema flexibility with zero join penalties."*

### Q4: "What happens if an admin changes a product's price or deletes it while an order is being processed?"
* **Your Answer (English):**
  > *"We implemented a two-fold safeguard:
  > First, **Price Snapshotting**: our `order_items` table explicitly freezes both `product_name` and `unit_price` at the millisecond of purchase, ensuring financial records are immutable.
  > Second, **Soft Deletes**: products are marked with `deleted_at` rather than physically deleted. Our `OrderService` uses `Product::withTrashed()`, ensuring historical orders can still display the product and restock operations during cancellation succeed seamlessly."*
