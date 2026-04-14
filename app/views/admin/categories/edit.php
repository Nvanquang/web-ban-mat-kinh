<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Category #<?= $category['id'] ?></h2>
    <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/categories/<?= $category['id'] ?>/edit">
                    <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($oldInput['category_name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Status</label>
                        <?php $currentStatus = isset($oldInput['status']) ? (int)$oldInput['status'] : 1; ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="status" id="status_1" value="1" <?= $currentStatus === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_1">
                                Visible <small class="text-muted">(shown in product filter)</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_0" value="0" <?= $currentStatus === 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_0">
                                Hidden <small class="text-muted">(hidden from customers)</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn text-white" style="background-color: #0ea5e9;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
