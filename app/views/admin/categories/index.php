<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Danh mục</h2>
</div>

<!-- Create Category Form -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Thêm danh mục mới</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/categories/create" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">

            <div class="col-md-4">
                <label for="category_name" class="form-label">
                    Tên danh mục <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="category_name" name="category_name"
                       value="<?= htmlspecialchars($oldInput['category_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label for="description" class="form-label">Mô tả</label>
                <input type="text" class="form-control" id="description" name="description"
                       value="<?= htmlspecialchars($oldInput['description'] ?? '') ?>"
                       placeholder="Mô tả tùy chọn...">
            </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-success d-block">
                    <i class="bi bi-plus-circle"></i> Thêm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="bi bi-tags fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Không tìm thấy danh mục</h5>
                <p class="text-muted">Thêm danh mục đầu tiên bằng biểu mẫu phía trên.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Sản phẩm</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($category['category_name']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($category['description'] ?? '') ?: '<em class="text-muted">Chưa có mô tả</em>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $category['product_count'] ?> sản phẩm
                                    </span>
                                </td>
                                <td>
                                    <?php if ($category['status']): ?>
                                        <span class="badge bg-success">Hiển thị</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?= BASE_URL ?>/admin/categories/<?= $category['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($category['product_count'] == 0): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/categories/<?= $category['id'] ?>/delete"
                                                  class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                    title="Không thể xóa: danh mục còn sản phẩm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
