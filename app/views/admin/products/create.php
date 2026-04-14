<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-plus-circle me-2 text-primary"></i>Thêm sản phẩm mới
        </h1>
        <p class="text-muted mb-0">Tạo sản phẩm mới cho danh mục của bạn</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay về danh sách sản phẩm
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="<?= BASE_URL ?>/admin/products/create" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">

            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        <h5 class="mb-0">Thông tin cơ bản</h5>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="product_name" name="product_name"
                                       value="<?= htmlspecialchars($oldInput['product_name'] ?? '') ?>" placeholder="Tên sản phẩm" required>
                                <label for="product_name">
                                    <i class="bi bi-tag me-1"></i>Tên sản phẩm <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Chọn danh mục...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"
                                            <?= ($oldInput['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="category_id">
                                    <i class="bi bi-tags me-1"></i>Danh mục <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="all" <?= ($oldInput['gender'] ?? 'all') == 'all' ? 'selected' : '' ?>>Unisex (Mọi đối tượng)</option>
                                    <option value="male" <?= ($oldInput['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Kính Nam</option>
                                    <option value="female" <?= ($oldInput['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Kính Nữ</option>
                                </select>
                                <label for="gender">
                                    <i class="bi bi-gender-ambiguous me-1"></i>Phân loại giới tính <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="price" name="price" min="0" step="1000"
                                       value="<?= htmlspecialchars($oldInput['price'] ?? '') ?>" placeholder="Giá" required>
                                <label for="price">
                                    <i class="bi bi-cash me-1"></i>Giá (₫) <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="old_price" name="old_price" min="0" step="1000"
                                       value="<?= htmlspecialchars($oldInput['old_price'] ?? '') ?>" placeholder="Giá cũ">
                                <label for="old_price">
                                    <i class="bi bi-cash-stack me-1"></i>Giá cũ (₫)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0"
                                       value="<?= htmlspecialchars($oldInput['stock_quantity'] ?? 0) ?>" placeholder="Số lượng tồn" required>
                                <label for="stock_quantity">
                                    <i class="bi bi-boxes me-1"></i>Số lượng tồn <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-floating">
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Mô tả sản phẩm..."><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
                            <label for="description">
                                <i class="bi bi-textarea me-1"></i>Mô tả
                            </label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-toggle-on me-1"></i>Trạng thái sản phẩm
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input" type="radio" name="status" id="status_selling"
                                           value="1" <?= ($oldInput['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label d-flex align-items-center" for="status_selling">
                                        <span class="badge bg-success me-2">●</span>
                                        <div>
                                            <strong>Đang bán</strong>
                                            <br>
                                            <small class="text-muted">Sản phẩm đang hoạt động và hiển thị với khách hàng</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input" type="radio" name="status" id="status_discontinued"
                                           value="0" <?= ($oldInput['status'] ?? 1) == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label d-flex align-items-center" for="status_discontinued">
                                        <span class="badge bg-secondary me-2">●</span>
                                        <div>
                                            <strong>Ngừng bán</strong>
                                            <br>
                                            <small class="text-muted">Sản phẩm không hoạt động và ẩn khỏi khách hàng</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-images me-2"></i>Ảnh sản phẩm
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="image-upload-area border-dashed border-2 border-primary rounded-3 p-4 text-center mb-3"
                                         id="imageUploadArea">
                                        <div class="upload-icon mb-3">
                                            <i class="bi bi-cloud-upload display-4 text-primary"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">Kéo & Thả ảnh vào đây</h5>
                                        <p class="text-muted small mb-3">hoặc nhấp để chọn tệp</p>
                                        <input type="file" class="d-none" id="image_url" name="image_url"
                                               accept="image/jpeg,image/png,image/webp">
                                        <button type="button" class="btn btn-primary" id="browseImagesBtn">
                                            <i class="bi bi-folder2-open me-1"></i>Chọn tệp
                                        </button>
                                        <div class="mt-2">
                                            <small class="text-muted">Định dạng: JPG, PNG, WEBP. Kích thước tối đa: 2MB.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="image-preview-container">
                                        <h6 class="mb-3">Ảnh đã chọn</h6>
                                        <div id="imagePreview" class="text-center">
                                            <div id="noImagesMessage" class="text-center text-muted py-4">
                                                <i class="bi bi-image display-6 mb-2"></i>
                                                <p class="mb-0">Chưa chọn ảnh</p>
                                            </div>
                                            <div id="image_preview" class="d-none">
                                                <img id="preview_img" src="" alt="Preview" class="img-fluid rounded shadow-sm"
                                                     style="max-width: 200px;">
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeImageBtn">
                                                        <i class="bi bi-trash me-1"></i>Xóa
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn">
                                    <i class="bi bi-check-circle me-2"></i>Tạo sản phẩm
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Trợ giúp & mẹo
                </h6>
            </div>
            <div class="card-body">
                <div class="accordion accordion-flush" id="helpAccordion">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#basicInfo">
                                <i class="bi bi-tag me-2"></i>Thông tin cơ bản
                            </button>
                        </h2>
                        <div id="basicInfo" class="accordion-collapse collapse"
                             data-bs-parent="#helpAccordion">
                            <div class="accordion-body small">
                                <p><strong>Tên sản phẩm:</strong> Chọn tên rõ ràng, dễ hiểu và hấp dẫn khách hàng.</p>
                                <p><strong>Danh mục:</strong> Chọn danh mục phù hợp để tổ chức sản phẩm tốt hơn.</p>
                                <p><strong>Giới tính:</strong> Phân loại sản phẩm dành cho Nam, Nữ hoặc Unisex, giúp khách hàng tìm kiếm chính xác hơn.</p>
                                <p><strong>Mô tả:</strong> Cung cấp thông tin chi tiết về tính năng và lợi ích.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#pricing">
                                <i class="bi bi-cash me-2"></i>Giá & tồn kho
                            </button>
                        </h2>
                        <div id="pricing" class="accordion-collapse collapse"
                             data-bs-parent="#helpAccordion">
                            <div class="accordion-body small">
                                <p><strong>Giá:</strong> Nhập giá bán hiện tại bằng Đồng Việt Nam (₫).</p>
                                <p><strong>Giá cũ:</strong> Tùy chọn. Hiển thị gạch ngang khi sản phẩm giảm giá.</p>
                                <p><strong>Tồn kho:</strong> Số lượng sản phẩm còn lại. Nhập 0 nếu hết hàng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#images">
                                <i class="bi bi-images me-2"></i>Ảnh sản phẩm
                            </button>
                        </h2>
                        <div id="images" class="accordion-collapse collapse"
                             data-bs-parent="#helpAccordion">
                            <div class="accordion-body small">
                                <p><strong>Yêu cầu ảnh:</strong> Định dạng JPG, PNG, hoặc WEBP, kích thước tối đa 2MB.</p>
                                <p><strong>Lựa chọn ảnh:</strong> Sử dụng hình ảnh chất lượng cao thể hiện sản phẩm một cách rõ ràng.</p>
                                <p><strong>Xem trước:</strong> Kiểm tra xem trước trước khi lưu để đảm bảo hình ảnh trông tốt.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced image upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image_url');
    const imageUploadArea = document.getElementById('imageUploadArea');
    const browseBtn = document.getElementById('browseImagesBtn');
    const preview = document.getElementById('image_preview');
    const previewImg = document.getElementById('preview_img');
    const noImagesMessage = document.getElementById('noImagesMessage');
    const removeBtn = document.getElementById('removeImageBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Browse button click
    browseBtn.addEventListener('click', function() {
        imageInput.click();
    });

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        imageUploadArea.classList.add('bg-light');
    }

    function unhighlight(e) {
        imageUploadArea.classList.remove('bg-light');
    }

    imageUploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if (files.length > 0) {
            imageInput.files = files;
            previewImage(files[0]);
        }
    }

    // File input change
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewImage(file);
        } else {
            hidePreview();
        }
    });

    // Remove image button
    removeBtn.addEventListener('click', function() {
        imageInput.value = '';
        hidePreview();
    });

    function previewImage(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
                noImagesMessage.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }
    }

    function hidePreview() {
        preview.classList.add('d-none');
        noImagesMessage.classList.remove('d-none');
    }

    // Form submission enhancement
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const spinner = submitBtn.querySelector('.spinner-border');
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Đang tạo sản phẩm... <span class="spinner-border spinner-border-sm ms-2" role="status"></span>';
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>