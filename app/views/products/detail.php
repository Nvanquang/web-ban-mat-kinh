<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php $related = $related ?? []; ?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products">Cửa hàng</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['product_name']) ?></li>
        </ol>
    </nav>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="product-image shadow rounded overflow-hidden">
                <img src="<?= BASE_URL ?>/public/uploads/<?= $product['image_url'] ?: 'no-image.jpg' ?>" class="img-fluid w-100" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="fw-bold mb-3"><?= htmlspecialchars($product['product_name']) ?></h1>
            <div class="mb-2 d-flex gap-2">
                <?php if (!empty($product['category_name'])): ?>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-tags me-1"></i><?= htmlspecialchars((string)$product['category_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
                <?php 
                    $g = $product['gender'] ?? 'all';
                    if ($g === 'male'): ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                        <i class="bi bi-gender-male me-1"></i>Kính Nam
                    </span>
                <?php elseif ($g === 'female'): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                        <i class="bi bi-gender-female me-1"></i>Kính Nữ
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                        <i class="bi bi-gender-ambiguous me-1"></i>Unisex
                    </span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3 mb-4">
                <h2 class="fw-bold mb-0" style="color:#0ea5e9;"><?= number_format((float)$product['price'], 0, ',', '.') ?> ₫</h2>
                <?php if (!empty($product['old_price'])): ?>
                    <del class="text-muted fs-4"><?= number_format((float)$product['old_price'], 0, ',', '.') ?> ₫</del>
                    <?php
                    $oldPrice = (float)$product['old_price'];
                    $price = (float)$product['price'];
                    $salePct = ($oldPrice > 0 && $oldPrice > $price) ? (int)round((1 - ($price / $oldPrice)) * 100) : null;
                    ?>
                    <?php if ($salePct !== null): ?>
                        <span class="badge text-white" style="background:#f59e0b;">SALE <?= $salePct ?>%</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php if ((int)($product['stock_quantity'] ?? 0) > 0): ?>
                    <span class="text-success fw-semibold">Stock: <?= (int)$product['stock_quantity'] ?> available</span>
                <?php else: ?>
                    <span class="text-danger fw-semibold">Out of stock</span>
                <?php endif; ?>
            </div>

            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <div class="d-flex gap-3">
                <form action="<?= BASE_URL ?>/cart/add" method="POST" data-ajax-cart="1" data-ajax-action="<?= BASE_URL ?>/cart/addAjax">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="mb-3" style="width: 220px;">
                        <label class="form-label fw-semibold mb-1">Quantity</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="qtyStep(-1)">−</button>
                            <input id="qty" type="number" name="quantity" class="form-control text-center" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="qtyStep(1)">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn text-white w-100 py-2" style="background:#0ea5e9;" <?= ((int)($product['stock_quantity'] ?? 0) <= 0) ? 'disabled' : '' ?>>
                        Add to Cart
                    </button>
                </form>
            </div>
            
            <hr class="my-5">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-4"></i>
                <span>Cam kết chính hãng 100%, bảo hành 12 tháng.</span>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="fw-bold mb-3">Description</h4>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?= nl2br(htmlspecialchars((string)($product['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <div class="mt-5 mb-5">
            <h4 class="fw-bold mb-3">Related Products</h4>
            <div class="row g-4">
                <?php foreach ($related as $rp): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm product-card">
                            <a href="<?= BASE_URL ?>/products/<?= (int)$rp['id'] ?>" class="product-img-wrapper">
                                <?php
                                $rawImg = (string)($rp['image_url'] ?? '');
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
                                <img
                                    src="<?= htmlspecialchars($imgSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                    class="product-img"
                                    alt="<?= htmlspecialchars((string)$rp['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                >
                            </a>
                            <div class="card-body">
                                <div class="product-title text-truncate mb-1" title="<?= htmlspecialchars((string)$rp['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string)$rp['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                </div>
                                <div class="product-price">
                                    <?= number_format((float)$rp['price'], 0, ',', '.') ?> ₫
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function qtyStep(delta) {
        const el = document.getElementById('qty');
        const current = parseInt(el.value || '1', 10);
        const next = Math.max(1, current + delta);
        el.value = String(next);
    }
</script>
<style>
    .product-card { transition: all .2s ease; }
    .product-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .10) !important; }
    .btn[style*="#0ea5e9"]:hover { background: #0284c7 !important; }

    .product-card{
        border: 1px solid rgba(15, 23, 42, .06);
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
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
    }
</style>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>
