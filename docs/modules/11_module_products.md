# Module: Products (Frontend)

## Objective
- Hiển thị danh sách sản phẩm với filter, search, sort, pagination
- Xem chi tiết sản phẩm với thông tin đầy đủ
- Hỗ trợ lọc theo danh mục, khoảng giá, từ khóa
- Tăng `view_count` mỗi khi xem chi tiết
- Nút "Add to Cart" chuyển sang CartController

---

## Wireframe thiết kế giao diện

### Product List — `/products`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR: Logo | Products | Cart(3) | Login]                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐  ┌─────────────────────────────────────────┐  │
│  │  FILTER     │  │  Products  (48 items)    Sort: [Newest▼]│  │
│  │─────────────│  │─────────────────────────────────────────│  │
│  │ Category    │  │  ┌──────────┐ ┌──────────┐ ┌──────────┐│  │
│  │ ○ All       │  │  │ [IMG]    │ │ [IMG]    │ │ [IMG]    ││  │
│  │ ● Prescription│ │  │          │ │  SALE    │ │          ││  │
│  │ ○ Sunglasses│  │  │ TR90     │ │ Rayban   │ │ Retro    ││  │
│  │ ○ Fashion   │  │  │ Frame    │ │ Classic  │ │ Fashion  ││  │
│  │ ○ Reading   │  │  │          │ │          │ │          ││  │
│  │─────────────│  │  │ 850,000₫ │ │~~1.2M~~ │ │ 450,000₫ ││  │
│  │ Price Range │  │  │ 2,200,000│ │          │ │~~600K~~ ││  │
│  │ Min: [____] │  │  │          │ │          │ │          ││  │
│  │ Max: [____] │  │  │[Add Cart]│ │[Add Cart]│ │[Add Cart]││  │
│  │ [Apply]     │  │  └──────────┘ └──────────┘ └──────────┘│  │
│  │─────────────│  │                                         │  │
│  │ [Clear All] │  │  ┌──────────┐ ┌──────────┐ ┌──────────┐│  │
│  └─────────────┘  │  │ ...      │ │ ...      │ │ ...      ││  │
│                   │  └──────────┘ └──────────┘ └──────────┘│  │
│                   │                                         │  │
│                   │  « 1 [2] 3 4 »                          │  │
│                   └─────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Product Detail — `/products/{id}`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
│  Home > Products > TR90 Prescription Frame                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌────────────────────────┐  ┌──────────────────────────────┐  │
│  │                        │  │  TR90 Prescription Frame     │  │
│  │                        │  │  ★★★★☆  Prescription         │  │
│  │    [PRODUCT IMAGE]     │  │                              │  │
│  │                        │  │  850,000 ₫                   │  │
│  │    500 × 400           │  │  ~~1,200,000 ₫~~   SALE 29% │  │
│  │                        │  │                              │  │
│  └────────────────────────┘  │  Stock: 50 available         │  │
│                              │                              │  │
│                              │  Quantity: [−] [1] [+]       │  │
│                              │                              │  │
│                              │  [   Add to Cart   ]         │  │  
│                              │  (bg: #0ea5e9, full width)   │  │
│                              │                              │  │
│                              │  ✓ Free shipping over 500K  │  │
│                              └──────────────────────────────┘  │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Description                                            │   │
│  │  Ultra-light titanium frame, suitable for all...        │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  Related Products                                               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ [IMG]    │ │ [IMG]    │ │ [IMG]    │ │ [IMG]    │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
└─────────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Product card: `bg #ffffff`, shadow `--shadow-sm`, hover `--shadow-hover` + translateY(-4px)
- SALE badge: `bg #f59e0b` (amber), text white, border-radius `--border-radius-sm`
- Price: `color #0ea5e9`, font-weight 700, font-size 1.25rem
- Old price: `text-decoration line-through`, `color --text-muted`
- Add to Cart button: `bg #0ea5e9`, hover `#0284c7`, border-radius `--border-radius-sm`
- Filter sidebar: `bg #ffffff`, border-right `--border-color`
- Pagination active: `bg #0ea5e9`, text white
- Grid: 3 columns desktop, 2 tablet, 1 mobile

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/products` | `ProductController::index` | Public |
| GET | `/products/{id}` | `ProductController::show` | Public |

---

## UI Pages

### `views/products/index.php`
- Sidebar filter: category radio, price range input, apply/clear button
- Sort dropdown: Newest, Price Low→High, Price High→Low, Most Popular
- Product grid (3 cols): image, name, price, old_price, sale badge, Add to Cart button
- Pagination
- Empty state nếu không có sản phẩm

### `views/products/detail.php`
- Breadcrumb: Home > Products > {product_name}
- Product image (large)
- Product name, category badge, price, old_price, sale %
- Stock status
- Quantity selector (JS +/-)
- Add to Cart button (POST form)
- Description tab
- Related products (cùng category, max 4)

---

## Data Processing Flow

### GET `/products`
```
1. Đọc query params:
   - $page       = max(1, (int)$_GET['page'] ?? 1)
   - $categoryId = (int)$_GET['category'] ?? 0
   - $minPrice   = (float)$_GET['min_price'] ?? 0
   - $maxPrice   = (float)$_GET['max_price'] ?? 0
   - $keyword    = trim($_GET['keyword'] ?? '')
   - $sort       = $_GET['sort'] ?? 'newest'
     (whitelist: newest, price_asc, price_desc, popular)

2. ProductModel::getPaginated($page, 12, $filters)
   → ['items', 'total', 'total_pages', 'current_page', ...]

3. GlassesCategoryModel::getAllVisible()
   → $categories (cho sidebar)

4. render('products/index', [
       'products'    => $result['items'],
       'pagination'  => $result,
       'categories'  => $categories,
       'filters'     => $filters,   // để giữ state filter/sort trên UI
       'title'       => 'Products'
   ])
```

### GET `/products/{id}`
```
1. $id = (int)$segments[1] ?? 0
   - $id <= 0 → redirect('/products')

2. ProductModel::getProductWithCategory($id)
   - not found hoặc status=0 → 404

3. ProductModel::incrementViewCount($id)

4. ProductModel::getRelated($id, $categoryId, limit=4)

5. render('products/detail', [
       'product'  => $product,
       'related'  => $related,
       'title'    => $product['product_name']
   ])
```

---

## Validation

### GET params (filter/sort)
| Param | Rule |
|---|---|
| `page` | cast int, min 1 |
| `category` | cast int, min 0 |
| `min_price` | cast float, min 0 |
| `max_price` | cast float, min 0; nếu < min_price → ignore |
| `sort` | whitelist: `newest, price_asc, price_desc, popular` |
| `keyword` | trim, max 100 ký tự, không cần escape (dùng LIKE ? trong PDO) |

### Product detail
| Check | Xử lý |
|---|---|
| `$id` không phải số hoặc ≤ 0 | redirect `/products` |
| Product không tồn tại trong DB | HTTP 404 |
| Product `status = 0` | HTTP 404 |

---

## Database Interaction

**Bảng chính:** `products`, `glasses_categories`

| Action | Method | Notes |
|---|---|---|
| Lấy danh sách có filter + pagination | `getPaginated(page, perPage, filters)` | JOIN glasses_categories |
| Lấy chi tiết + tên category | `getProductWithCategory(id)` | LEFT JOIN |
| Tăng view | `incrementViewCount(id)` | UPDATE products SET view_count + 1 |
| Lấy sản phẩm liên quan | `getRelated(id, categoryId, limit)` | WHERE category_id = ? AND id != ? |
| Lấy tất cả danh mục visible | `GlassesCategoryModel::getAllVisible()` | WHERE status = 1 |

---

## Permissions

- Tất cả route Public — không cần login
- Không có action nào yêu cầu quyền đặc biệt

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| `$id` invalid (không phải số) | redirect `/products` |
| Product không tồn tại | HTTP 404, render `errors/404` |
| Product `status = 0` | HTTP 404 (không lộ "product discontinued") |
| DB lỗi | HTTP 500, log error, render `errors/500` |
| Không có sản phẩm nào match filter | Hiển thị empty state với message + nút "Clear Filters" |

---

## Redirect Rules

| Sự kiện | Redirect |
|---|---|
| `$id` invalid | `/products` |
| Product 404/disabled | `errors/404` (HTTP 404) |
| Add to Cart thành công | `/cart` (xử lý bởi CartController) |

---

## Done ✅

- [ ] GET `/products` render danh sách với 3-col grid
- [ ] Filter theo category hoạt động (radio sidebar)
- [ ] Filter theo min/max price hoạt động
- [ ] Search theo keyword hoạt động
- [ ] Sort (newest, price, popular) hoạt động
- [ ] Pagination đúng (total pages, current page active)
- [ ] Filter state giữ nguyên khi chuyển trang (query string)
- [ ] GET `/products/{id}` render đúng, đủ thông tin
- [ ] `view_count` tăng mỗi lần xem detail
- [ ] Related products hiển thị (cùng category, trừ chính nó)
- [ ] SALE badge hiển thị khi `old_price` không null
- [ ] Out of stock hiển thị khi `stock_quantity = 0`
- [ ] HTTP 404 đúng khi product không tồn tại hoặc disabled
