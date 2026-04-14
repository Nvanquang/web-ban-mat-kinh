<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam me-2 text-primary"></i>Products
        </h1>
        <p class="text-muted mb-0">Quản lý sản phẩm</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-circle me-2"></i>Thêm sản phẩm
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-search me-2 text-primary"></i>
            <h6 class="mb-0">Tìm kiếm & Lọc</h6>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/products" class="row g-3">
            <div class="col-md-4">
                <label for="keyword" class="form-label">
                    <i class="bi bi-input-cursor-text me-1"></i>Tìm sản phẩm
                </label>
                <input type="text" class="form-control" id="keyword" name="keyword"
                       value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>"
                       placeholder="Tìm theo tên sản phẩm...">
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label">
                    <i class="bi bi-tags me-1"></i>Danh mục
                </label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"
                            <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="gender" class="form-label">
                    <i class="bi bi-gender-ambiguous me-1"></i>Giới tính
                </label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Tất cả giới tính</option>
                    <option value="all" <?= ($filters['gender'] ?? '') == 'all' ? 'selected' : '' ?>>Unisex (Tất cả)</option>
                    <option value="male" <?= ($filters['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Nam</option>
                    <option value="female" <?= ($filters['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Nữ</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bi bi-table me-2 text-primary"></i>
                <h6 class="mb-0">Danh sách sản phẩm</h6>
            </div>
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Hiển thị <?= count($products) ?> trên tổng số <?= $pagination['total'] ?> sản phẩm
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="empty-state-title">Không tìm thấy sản phẩm</div>
                <div class="empty-state-text">
                    <?php if (!empty($filters['keyword'] ?? '') || !empty($filters['category_id'] ?? '')): ?>
                        Vui lòng điều chỉnh tiêu chí tìm kiếm hoặc xóa bộ lọc để xem lại sản phẩm.
                    <?php else: ?>
                        Bắt đầu xây dựng danh mục bằng cách thêm sản phẩm đầu tiên.
                    <?php endif; ?>
                </div>
                <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Thêm sản phẩm đầu tiên
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">#</th>
                            <th class="border-0">Sản phẩm</th>
                            <th class="border-0">Giá</th>
                            <th class="border-0">Phân loại</th>
                            <th class="border-0">Tồn kho</th>
                            <th class="border-0">Trạng thái</th>
                            <th class="border-0 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="fw-bold text-primary">#<?= $product['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                                                 alt="Product" class="product-image me-3 rounded">
                                        <?php else: ?>
                                            <div class="bg-light product-image me-3 rounded d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-muted fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold mb-1">
                                                <a href="<?= BASE_URL ?>/products/<?= $product['id'] ?>" class="text-decoration-none text-dark" target="_blank">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                </a>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-tags me-1"></i>
                                                <?= htmlspecialchars($product['category_name'] ?? 'Không có danh mục') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold text-primary">
                                            <?= number_format($product['price'], 0, ',', '.') ?> đ
                                        </span>
                                        <?php if ($product['old_price']): ?>
                                            <br>
                                            <small class="text-muted text-decoration-line-through">
                                                <?= number_format($product['old_price'], 0, ',', '.') ?> đ
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (($product['gender'] ?? 'all') === 'male'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="bi bi-gender-male me-1"></i>Nam
                                        </span>
                                    <?php elseif (($product['gender'] ?? 'all') === 'female'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-gender-female me-1"></i>Nữ
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-gender-ambiguous me-1"></i>Unisex
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge fs-6 px-3 py-2 <?= $product['stock_quantity'] > 10 ? 'bg-success' : ($product['stock_quantity'] > 0 ? 'bg-warning' : 'bg-danger') ?>">
                                        <i class="bi bi-<?= $product['stock_quantity'] > 10 ? 'check-circle' : ($product['stock_quantity'] > 0 ? 'exclamation-triangle' : 'x-circle') ?> me-1"></i>
                                        <?= $product['stock_quantity'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge fs-6 px-3 py-2 bg-<?= $product['status'] == 1 ? 'success' : 'secondary' ?>">
                                        <i class="bi bi-<?= $product['status'] == 1 ? 'eye' : 'eye-slash' ?> me-1"></i>
                                        <?= $product['status'] == 1 ? 'Đang bán' : 'Ngừng bán' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/admin/products/<?= $product['id'] ?>/edit?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                           class="btn btn-sm btn-outline-primary" title="Sửa sản phẩm" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/products/<?= $product['id'] ?>/delete"
                                              class="d-inline delete-product-form"
                                              data-product-id="<?= $product['id'] ?>"
                                              data-product-name="<?= htmlspecialchars($product['product_name']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa sản phẩm" data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="card-footer bg-light border-top">
                    <nav aria-label="Product pagination">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&category_id=<?= $filters['category_id'] ?? '' ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            ?>

                            <?php if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&category_id=<?= $filters['category_id'] ?? '' ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&category_id=<?= $filters['category_id'] ?? '' ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $pagination['total_pages']): ?>
                                <?php if ($end < $pagination['total_pages'] - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['total_pages'] ?>&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&category_id=<?= $filters['category_id'] ?? '' ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>">
                                        <?= $pagination['total_pages'] ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&category_id=<?= $filters['category_id'] ?? '' ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
