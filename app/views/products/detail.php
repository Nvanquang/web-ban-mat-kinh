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
            <div class="d-flex align-items-center gap-3 mb-4">
                <h2 class="text-danger fw-bold mb-0"><?= number_format($product['price'], 0, ',', '.') ?>đ</h2>
                <?php if (!empty($product['old_price'])): ?>
                    <del class="text-muted fs-4"><?= number_format($product['old_price'], 0, ',', '.') ?>đ</del>
                <?php endif; ?>
            </div>

            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <div class="d-flex gap-3">
                <form action="<?= BASE_URL ?>/cart/add" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="input-group mb-3" style="width: 130px;">
                        <span class="input-group-text">SL</span>
                        <input type="number" name="quantity" class="form-control" value="1" min="1">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg px-5">Thêm vào giỏ hàng</button>
                </form>
            </div>
            
            <hr class="my-5">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-4"></i>
                <span>Cam kết chính hãng 100%, bảo hành 12 tháng.</span>
            </div>
        </div>
    </div>
</div>
