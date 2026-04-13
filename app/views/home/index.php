<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center text-md-start">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-md-6 mb-5 mb-md-0">
                <span class="badge bg-primary rounded-pill px-3 py-2 mb-3 shadow-sm" style="font-size: 0.9rem;">Bộ Sưu Tập Mới 2026</span>
                <h1 class="hero-title">Định Hình<br><span class="text-primary">Phong Cách</span> Của Bạn</h1>
                <p class="hero-subtitle">Khám phá hàng trăm mẫu kính thời trang, bảo vệ mắt tối ưu cùng dịch vụ tư vấn online chuyên nghiệp ngay tại nhà.</p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                    <a href="shop.php" class="btn btn-primary btn-lg btn-custom shadow-sm"><i class="bi bi-cart3 me-2"></i>Mua Ngay</a>
                    <a href="shop.php" class="btn btn-outline-dark btn-lg btn-custom">Xem Chi Tiết</a>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Using a high quality placeholder from Unsplash -->
                <img src="https://images.unsplash.com/photo-1577803645773-f96470509666?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Người mẫu đeo kính" class="img-fluid rounded-4 shadow-lg" style="transform: rotate(-3deg); transition: transform 0.3s; border: 5px solid white;" onmouseover="this.style.transform='rotate(0deg)'" onmouseout="this.style.transform='rotate(-3deg)'">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">🚀</div>
                    <h5>Giao Hàng Siêu Tốc</h5>
                    <p class="text-muted mb-0">Miễn phí giao hàng cho đơn hàng từ 500k. Nhận hàng sau 2H tại nội thành.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">✨</div>
                    <h5>Dùng Thử Tại Nhà</h5>
                    <p class="text-muted mb-0">Thoải mái thử nghiệm lên đến 3 mẫu kính tại nhà bạn mà không mất phí.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">💎</div>
                    <h5>Chất Lượng Cao Cấp</h5>
                    <p class="text-muted mb-0">Cam kết hàng chính hãng 100%, bảo hành lỗi 1 đổi 1 trong vòng 30 ngày.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-white rounded-top-5 mt-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-primary fw-bold text-uppercase mb-1">Bán Chạy Nhất</h6>
                <h2 class="fw-bold mb-0" style="color: #2c3e50;">Sản Phẩm Nổi Bật</h2>
            </div>
            <a href="<?= BASE_URL ?>/products" class="text-decoration-none fw-semibold">Xem tất cả &rarr;</a>
        </div>
        
        <div class="row g-4" id="featured-products">
            <?php foreach ($products as $p): ?>
                <?php
                $isHot = !empty($p['is_custom']);
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
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 position-relative shadow-sm product-card">
                        <span class="badge badge-custom shadow-sm <?= $isHot ? 'bg-danger' : 'bg-success' ?>">
                            <?php if ($isHot): ?>
                                <i class="bi bi-fire"></i> HOT
                            <?php else: ?>
                                MỚI
                            <?php endif; ?>
                        </span>

                        <a href="<?= BASE_URL ?>/products/<?= (int)$p['id'] ?>" class="product-img-wrapper">
                            <img
                                src="<?= htmlspecialchars($imgSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                class="product-img"
                                alt="<?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                            >
                        </a>
                        <div class="card-body text-center d-flex flex-column p-4">
                            <h5 class="product-title mb-2 text-truncate" title="<?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                <?= htmlspecialchars((string)$p['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                            </h5>
                            <p class="product-price mt-auto mb-3">
                                <?= number_format((float)$p['price'], 0, ',', '.') ?>₫
                            </p>

                            <form method="POST" action="<?= BASE_URL ?>/cart/add" class="mt-auto">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-outline-primary w-100 btn-custom">
                                    <i class="bi bi-cart-plus"></i> Thêm Giỏ Hàng
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .badge-custom{
        position:absolute;
        top:12px;
        left:12px;
        z-index:2;
        border-radius:999px;
        padding:.45rem .6rem;
        font-weight:700;
        font-size:.75rem;
    }

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
        font-size: 1.05rem;
    }
    .btn-custom{ border-radius: .75rem; }
</style>

<!-- Newsletter / Call To Action -->
<section class="py-5 mb-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 text-center p-5 bg-dark text-white rounded-4 shadow-lg position-relative overflow-hidden" style="background: linear-gradient(45deg, #1e3c72, #2a5298) !important;">
                <div style="position: absolute; top: -50%; left: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                <div style="position: absolute; bottom: -50%; right: -10%; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                
                <h3 class="fw-bold position-relative z-1 mb-3">Nhận Ưu Đãi Độc Quyền!</h3>
                <p class="position-relative z-1 mb-4 text-light" style="font-size: 1.1rem;">Đăng ký email để nhận ngay mã giảm giá <span class="fw-bold text-warning">15%</span> cho đơn hàng đầu tiên của bạn.</p>
                <form class="d-flex flex-column flex-sm-row justify-content-center gap-2 mx-auto position-relative z-1" style="max-width: 500px;">
                    <input type="email" class="form-control rounded-pill px-4 py-3" placeholder="Nhập địa chỉ email của bạn..." required>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 py-3 fw-bold text-dark shadow-sm">Đăng Ký Khuyến Mãi</button>
                </form>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

