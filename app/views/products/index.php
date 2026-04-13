<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php
$filters = $filters ?? [
    'category_id' => 0,
    'min_price'   => 0,
    'max_price'   => 0,
    'keyword'     => '',
    'sort'        => 'newest',
];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false];
$categories = $categories ?? [];
?>

<div class="container py-4">
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fw-bold mb-3">FILTER</div>

                    <form method="GET" action="<?= BASE_URL ?>/products">
                        <div class="mb-3">
                            <div class="fw-semibold mb-2">Category</div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat_all" value="0"
                                    <?= ((int)($filters['category_id'] ?? 0) === 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat_all">All</label>
                            </div>
                            <?php foreach ($categories as $cat): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" id="cat_<?= (int)$cat['id'] ?>" value="<?= (int)$cat['id'] ?>"
                                        <?= ((int)($filters['category_id'] ?? 0) === (int)$cat['id']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cat_<?= (int)$cat['id'] ?>">
                                        <?= htmlspecialchars((string)$cat['category_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-2">Price Range</div>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-1">Min</label>
                                <input type="number" class="form-control" name="min_price" min="0" step="1000"
                                    value="<?= htmlspecialchars((string)($filters['min_price'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label class="form-label small text-muted mb-1">Max</label>
                                <input type="number" class="form-control" name="max_price" min="0" step="1000"
                                    value="<?= htmlspecialchars((string)($filters['max_price'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-2">Keyword</div>
                            <input type="text" class="form-control" name="keyword" maxlength="100"
                                value="<?= htmlspecialchars((string)($filters['keyword'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                placeholder="Search...">
                        </div>

                        <input type="hidden" name="sort" value="<?= htmlspecialchars((string)($filters['sort'] ?? 'newest'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">

                        <button type="submit" class="btn btn-primary w-100">Apply</button>

                        <a class="btn btn-link w-100 mt-2 text-decoration-none" href="<?= BASE_URL ?>/products">Clear All</a>
                    </form>
                </div>
            </div>
        </aside>

        <section class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h2 class="fw-bold mb-0">Products</h2>
                    <div class="text-muted small"><?= (int)($pagination['total'] ?? 0) ?> items</div>
                </div>

                <form method="GET" action="<?= BASE_URL ?>/products" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="category" value="<?= (int)($filters['category_id'] ?? 0) ?>">
                    <input type="hidden" name="min_price" value="<?= htmlspecialchars((string)($filters['min_price'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                    <input type="hidden" name="max_price" value="<?= htmlspecialchars((string)($filters['max_price'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                    <input type="hidden" name="keyword" value="<?= htmlspecialchars((string)($filters['keyword'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">

                    <label class="text-muted small">Sort:</label>
                    <select name="sort" class="form-select" style="width: 200px;" onchange="this.form.submit()">
                        <option value="newest" <?= ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
                        <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                        <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="text-muted fs-5 mb-3">No products match your filters.</div>
                        <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/products">Clear Filters</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <?php
                        $oldPrice = $p['old_price'] ?? null;
                        $price = (float)($p['price'] ?? 0);
                        $salePct = null;
                        if ($oldPrice !== null && (float)$oldPrice > 0 && (float)$oldPrice > $price) {
                            $salePct = (int)round((1 - ($price / (float)$oldPrice)) * 100);
                        }
                        $rawImg = (string)($p['image_url'] ?? '');
                        if ($rawImg === '') {
                            $imgSrc = BASE_URL . '/public/uploads/no-image.jpg';
                        } elseif (preg_match('~^https?://~i', $rawImg)) {
                            $imgSrc = $rawImg;
                        } elseif (str_starts_with($rawImg, 'public/uploads/')) {
                            $imgSrc = BASE_URL . '/' . $rawImg;
                        } else {
                            $imgSrc = BASE_URL . '/public/uploads/' . ltrim($rawImg, '/');
                        }
                        ?>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="card h-100 shadow-sm product-card">
                                <a href="<?= BASE_URL ?>/products/<?= (int)$p['id'] ?>" class="product-img-wrapper">
                                    <div class="position-relative">
                                        <img
                                            src="<?= htmlspecialchars($imgSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                            class="product-img"
                                            alt="<?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                        >
                                        <?php if ($salePct !== null): ?>
                                            <span class="badge position-absolute top-0 start-0 m-2 text-white" style="background:#f59e0b;">
                                                SALE <?= $salePct ?>%
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="card-body">
                                    <div class="product-title text-truncate mb-1" title="<?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 mb-3">
                                        <div class="product-price">
                                            <?= number_format((float)$p['price'], 0, ',', '.') ?> ₫
                                        </div>
                                        <?php if (!empty($p['old_price'])): ?>
                                            <div class="text-muted text-decoration-line-through small">
                                                <?= number_format((float)$p['old_price'], 0, ',', '.') ?> ₫
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" action="<?= BASE_URL ?>/cart/add">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn text-white w-100" style="background:#0ea5e9;">
                                            Add Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                    <?php
                    $queryBase = [
                        'category'  => (int)($filters['category_id'] ?? 0),
                        'min_price' => $filters['min_price'] ?? 0,
                        'max_price' => $filters['max_price'] ?? 0,
                        'keyword'   => $filters['keyword'] ?? '',
                        'sort'      => $filters['sort'] ?? 'newest',
                    ];
                    ?>
                    <nav class="mt-4" aria-label="Products pagination">
                        <ul class="pagination justify-content-center">
                            <?php if (!empty($pagination['has_prev'])): ?>
                                <?php $queryBase['page'] = (int)$pagination['current_page'] - 1; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/products?<?= http_build_query($queryBase) ?>">«</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= (int)$pagination['total_pages']; $i++): ?>
                                <?php $queryBase['page'] = $i; ?>
                                <li class="page-item <?= $i === (int)$pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/products?<?= http_build_query($queryBase) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if (!empty($pagination['has_next'])): ?>
                                <?php $queryBase['page'] = (int)$pagination['current_page'] + 1; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/products?<?= http_build_query($queryBase) ?>">»</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<style>
    /* Match Home page product card effects */
    .product-card{
        border: 1px solid rgba(15, 23, 42, .06);
        border-radius: 1rem;
        overflow: hidden;
        transition: all .25s ease;
        background: #fff;
    }
    .product-card:hover{
        transform: translateY(-4px);
        box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .10) !important;
    }
    .product-img-wrapper{
        display:block;
        overflow:hidden;
        background:#f8fafc;
        text-decoration:none;
    }
    .product-img{
        width:100%;
        height:auto;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        transition: transform .35s ease;
    }
    .product-card:hover .product-img{
        transform: scale(1.06);
    }
    .product-title{
        font-weight: 700;
        color: #2c3e50;
    }
    .product-price{
        font-weight: 800;
        color:#0ea5e9;
        font-size:1.1rem;
    }
    .pagination .page-item.active .page-link { background: #0ea5e9; border-color: #0ea5e9; }
    .btn[style*="#0ea5e9"]:hover { background: #0284c7 !important; }
</style>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>
