<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Điều chỉnh sản phẩm</h2>
    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Trở về danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin sản phẩm</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/products/<?= $product['id'] ?>/edit" enctype="multipart/form-data"
                      class="update-product-form" data-product-id="<?= $product['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($returnUrl ?? BASE_URL . '/admin/products') ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_name" class="form-label">
                                Tên sản phẩm <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="product_name" name="product_name"
                                   value="<?= htmlspecialchars($oldInput['product_name'] ?? $product['product_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">
                                Danh mục <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= ($oldInput['category_id'] ?? $product['category_id']) == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="gender" class="form-label">
                                Phân loại giới tính <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="all" <?= ($oldInput['gender'] ?? $product['gender'] ?? 'all') == 'all' ? 'selected' : '' ?>>Unisex (Mọi đối tượng)</option>
                                <option value="male" <?= ($oldInput['gender'] ?? $product['gender']) == 'male' ? 'selected' : '' ?>>Kính Nam</option>
                                <option value="female" <?= ($oldInput['gender'] ?? $product['gender']) == 'female' ? 'selected' : '' ?>>Kính Nữ</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">
                                Giá (₫) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="1000"
                                   value="<?= htmlspecialchars($oldInput['price'] ?? $product['price']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="old_price" class="form-label">Giá cũ (₫)</label>
                            <input type="number" class="form-control" id="old_price" name="old_price" min="0" step="1000"
                                   value="<?= htmlspecialchars($oldInput['old_price'] ?? $product['old_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="stock_quantity" class="form-label">
                                Số lượng tồn kho <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0"
                                   value="<?= htmlspecialchars($oldInput['stock_quantity'] ?? $product['stock_quantity']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                                  placeholder="Mô tả sản phẩm..."><?= htmlspecialchars($oldInput['description'] ?? $product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_selling"
                                   value="1" <?= ($oldInput['status'] ?? $product['status']) == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_selling">
                                <span class="badge bg-success me-1">●</span> Đang bán
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_discontinued"
                                   value="0" <?= ($oldInput['status'] ?? $product['status']) == 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_discontinued">
                                <span class="badge bg-secondary me-1">●</span> Ngừng bán
                            </label>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Ảnh sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($product['image_url']): ?>
                                <div class="mb-3">
                                    <label class="form-label">Ảnh hiện tại</label>
                                    <div>
                                        <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                                             alt="Current product image" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="image_url" class="form-label">
                                    <?= $product['image_url'] ? 'Change Image (optional)' : 'Upload Image' ?>
                                </label>
                                <input type="file" class="form-control" id="image_url" name="image_url"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">
                                    Chỉ chấp nhận file ảnh JPEG, PNG hoặc WEBP. Kích thước tối đa 2MB.
                                    <?= $product['image_url'] ? 'Leave empty to keep current image.' : '' ?>
                                </div>
                            </div>
                            <div id="image_preview" class="d-none">
                                <label class="form-label">Xem trước ảnh</label>
                                <img id="preview_img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Cập nhật sản phẩm
                        </button>
                        <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Thông tin sản phẩm</h6>
            </div>
            <div class="card-body">
                <dl class="row small">
                    <dt class="col-sm-5">Mã sản phẩm:</dt>
                    <dd class="col-sm-7">#<?= $product['id'] ?></dd>

                    <dt class="col-sm-5">Đã tạo:</dt>
                    <dd class="col-sm-7"><?= date('M j, Y', strtotime($product['created_at'])) ?></dd>

                    <dt class="col-sm-5">Lượt xem:</dt>
                    <dd class="col-sm-7"><?= $product['view_count'] ?? 0 ?></dd>

                    <dt class="col-sm-5">Giới tính:</dt>
                    <dd class="col-sm-7">
                        <?php 
                        $g = $product['gender'] ?? 'all';
                        echo $g === 'male' ? 'Nam' : ($g === 'female' ? 'Nữ' : 'Unisex');
                        ?>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Gợi ý & Mẹo</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><strong>Tên sản phẩm:</strong> Giữ cho tên sản phẩm mô tả và độc đáo.</li>
                    <li class="mb-2"><strong>Giá:</strong> Nhập giá bán hiện tại bằng VND.</li>
                    <li class="mb-2"><strong>Giá cũ:</strong> Tùy chọn, để hiển thị giảm giá.</li>
                    <li class="mb-2"><strong>Kho hàng:</strong> Đặt thành 0 nếu hết hàng.</li>
                    <li class="mb-2"><strong>Ảnh:</strong> Hình ảnh vuông sẽ hoạt động tốt nhất (tỷ lệ 1:1).</li>
                    <li><strong>Trạng thái:</strong> "Đang bán" cho các sản phẩm hoạt động, "Ngừng bán" cho các sản phẩm không hoạt động.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
document.getElementById('image_url').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image_preview');
    const previewImg = document.getElementById('preview_img');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
});
</script>