# Module: Admin — Reports & Statistics

## Objective
- Báo cáo doanh thu theo tháng/năm (bảng + biểu đồ đơn giản)
- Thống kê đơn hàng theo trạng thái
- Top sản phẩm bán chạy nhất
- Top khách hàng mua nhiều nhất
- Tất cả chỉ là read-only — không có action CRUD

---

## Wireframe thiết kế giao diện

### Reports Page — `/admin/reports`
```
┌───────────────────────────────────────────────────────────────┐
│  [TOPBAR]                                                     │
├──────────┬────────────────────────────────────────────────────┤
│ SIDEBAR  │                                                    │
│ ...      │  Reports & Statistics                              │
│►Reports  │                                                    │
│          │  Year: [2025 ▼]     [  Apply  ]                    │
│          │                                                    │
│          │  ┌──────────────────────────────────────────────┐  │
│          │  │  Revenue by Month — 2025                     │  │
│          │  │  ─────────────────────────────────────────   │  │
│          │  │                                              │  │
│          │  │  Jan ████████████████  12,500,000 ₫          │  │
│          │  │  Feb ████████████      8,200,000 ₫           │  │
│          │  │  Mar ██████████████████ 15,400,000 ₫         │  │
│          │  │  Apr ████████          6,100,000 ₫           │  │
│          │  │  May (no data)              0 ₫              │  │
│          │  │  ...                                         │  │
│          │  │  ─────────────────────────────────────────   │  │
│          │  │  Total 2025:          42,200,000 ₫           │  │
│          │  └──────────────────────────────────────────────┘  │
│          │                                                    │
│          │  ┌──────────────────┐  ┌──────────────────────┐   │
│          │  │ Orders by Status │  │ Top Products         │   │
│          │  │ ────────────────  │  │ ───────────────────  │   │
│          │  │ pending     12   │  │ 1. Rayban     89 sold│   │
│          │  │ confirmed    8   │  │ 2. TR90       72 sold│   │
│          │  │ shipped      5   │  │ 3. Retro      55 sold│   │
│          │  │ completed   95   │  │ 4. Korean     41 sold│   │
│          │  │ cancelled    8   │  │ 5. Reading    28 sold│   │
│          │  └──────────────────┘  └──────────────────────┘   │
│          │                                                    │
│          │  ┌──────────────────────────────────────────────┐  │
│          │  │  Top Customers — 2025                        │  │
│          │  │  ─────────────────────────────────────────   │  │
│          │  │  # │ Name        │ Orders │ Total Spent      │  │
│          │  │  1 │ John Doe    │   12   │  24,500,000 ₫    │  │
│          │  │  2 │ Alice Smith │    8   │  18,200,000 ₫    │  │
│          │  │  3 │ Bob Lee     │    6   │  12,100,000 ₫    │  │
│          │  └──────────────────────────────────────────────┘  │
└──────────┴────────────────────────────────────────────────────┘
```

**Style notes:**
- Year filter: `select` + Apply button (`bg #0ea5e9`)
- Revenue bar chart: CSS-only horizontal bars, `bg #0ea5e9`, `border-radius 4px`, max-width proportional
- Bar width: `width: calc(revenue / max_revenue * 100%)` — tính trong PHP, inject vào `style`
- Orders by status: compact table, badge màu theo status (giống modules khác)
- Top products / Top customers: `bg #ffffff`, shadow `--shadow-sm`
- Total row: `font-weight 700`, `color #0ea5e9`, `border-top 2px solid --border-color`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/admin/reports` | `AdminReportController::index` | Admin |

---

## UI Pages

### `views/admin/reports/index.php`
- Year selector form (GET, action="/admin/reports")
- Revenue by month: horizontal bar chart (CSS-only) + bảng tháng + tổng năm
- Orders by status: bảng đếm theo status
- Top 10 sản phẩm bán chạy: rank, product_name, total_sold, revenue
- Top 10 customers: rank, full_name, order_count, total_spent

---

## Data Processing Flow

### GET `/admin/reports`
```
1. requireAdmin()
2. $year = (int)($_GET['year'] ?? date('Y'))
   - $year < 2020 || $year > date('Y') → $year = (int)date('Y')
3. OrderModel::getRevenueByMonth($year)
   → array of 12 months [month, order_count, revenue]
   → Tính $maxRevenue = max(revenue values) — để tính width % bar chart
   → Tính $totalRevenue = sum(revenue values)
4. OrderModel::countByStatus()
   → ['pending'=>n, 'confirmed'=>n, ...]
5. ProductModel::getTopSellingProducts(10)
6. CustomerModel::getTopCustomers($year, 10)
7. render('admin/reports/index', [
       year, revenueData, maxRevenue, totalRevenue,
       ordersByStatus, topProducts, topCustomers
   ], 'admin')
```

---

## Validation

| Param | Rule | Fallback |
|---|---|---|
| `year` (GET) | cast int, range 2020–current year | current year |

---

## Database Interaction

**Bảng:** `orders`, `order_details`, `products`, `customers`

| Action | Method | SQL Notes |
|---|---|---|
| Doanh thu theo tháng | `OrderModel::getRevenueByMonth($year)` | GROUP BY MONTH(order_date), WHERE status='completed', YEAR=? |
| Đếm theo status | `OrderModel::countByStatus()` | GROUP BY status (tất cả năm) |
| Top sản phẩm | `ProductModel::getTopSellingProducts(10)` | JOIN order_details + orders, GROUP BY product, ORDER BY SUM(quantity) DESC |
| Top customers | `CustomerModel::getTopCustomers($year, 10)` | JOIN orders, WHERE YEAR(order_date)=?, GROUP BY customer, ORDER BY SUM(total_amount) DESC |

### SQL mẫu: `getRevenueByMonth`
```sql
SELECT
    MONTH(order_date)  AS month,
    COUNT(*)           AS order_count,
    COALESCE(SUM(total_amount), 0) AS revenue
FROM orders
WHERE YEAR(order_date) = ?
  AND status = 'completed'
GROUP BY MONTH(order_date)
ORDER BY month ASC
```
> Các tháng không có data sẽ không xuất hiện — PHP fill 0 cho tháng còn thiếu.

### SQL mẫu: `getTopCustomers`
```sql
SELECT
    c.id,
    c.full_name,
    c.email,
    COUNT(o.id)          AS order_count,
    SUM(o.total_amount)  AS total_spent
FROM customers c
JOIN orders o ON c.id = o.customer_id
WHERE YEAR(o.order_date) = ?
  AND o.status = 'completed'
  AND c.role = 'customer'
GROUP BY c.id, c.full_name, c.email
ORDER BY total_spent DESC
LIMIT ?
```

---

## Permissions

- Route: Admin only — `requireAdmin()` trong constructor
- Report chỉ đọc — không có POST action nào

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| `year` ngoài range | Fallback về năm hiện tại, không flash error |
| Không có data cho năm được chọn | Hiển thị bảng trống + "No data for {year}" |
| DB error | HTTP 500, log, render `errors/500` |

---

## Redirect Rules

- Không có redirect — module hoàn toàn read-only (GET only)

---

## Done ✅

- [ ] GET `/admin/reports` render đủ 4 sections
- [ ] Year selector hoạt động (GET param)
- [ ] Revenue bar chart hiển thị đúng tỷ lệ width (CSS-based)
- [ ] Các tháng không có data hiển thị 0 (không bị thiếu row)
- [ ] Tổng doanh thu cả năm tính đúng
- [ ] Orders by status đếm đúng
- [ ] Top 10 products sort theo total_sold DESC
- [ ] Top 10 customers sort theo total_spent DESC
- [ ] Năm ngoài range fallback về năm hiện tại
- [ ] Admin guard hoạt động
