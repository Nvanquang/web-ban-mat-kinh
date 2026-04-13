<?php require_once APPROOT . '/app/views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">Cửa hàng</h2>
        </div>
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/products" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn btn-primary">Tìm</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-4">Không tìm thấy sản phẩm nào.</p>
            </div>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</div>

<?php require_once APPROOT . '/app/views/partials/footer.php'; ?>
