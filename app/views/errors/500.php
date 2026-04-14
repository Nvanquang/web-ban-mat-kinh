<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="text-center py-5">
        <h1 class="fw-bold mb-2">500</h1>
        <p class="text-muted mb-4">Đã xảy ra lỗi. Vui lòng thử lại sau.</p>
        <a class="btn btn-primary" href="javascript:history.back();">Quay lại trang trước đó</a>
        <a class="btn btn-outline-secondary ms-2" href="<?= BASE_URL ?>">Trang chủ</a>
    </div>
</div>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

