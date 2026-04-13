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
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold">Sản Phẩm Nổi Bật</h2>
            </div>
            <a href="<?= BASE_URL ?>/products" class="text-decoration-none">Xem tất cả &rarr;</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($products as $p): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <a href="<?= BASE_URL ?>/products/show/<?= $p['id'] ?>">
                            <img src="<?= BASE_URL ?>/public/uploads/<?= $p['image_url'] ?: 'no-image.jpg' ?>" class="card-img-top" alt="<?= htmlspecialchars($p['product_name']) ?>">
                        </a>
                        <div class="card-body text-center">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($p['product_name']) ?></h5>
                            <p class="text-danger fw-bold"><?= number_format($p['price'], 0, ',', '.') ?>đ</p>
                            <a href="<?= BASE_URL ?>/products/show/<?= $p['id'] ?>" class="btn btn-outline-primary w-100">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

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

