<?php require_once APPROOT . '/app/views/partials/header.php'; ?>

<!-- Main Content Area -->
<main class="flex-grow-1">
    <!-- Flash Messages -->
    <?php $flash = Session::flash(); if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php require_once APPROOT . '/app/views/partials/footer.php'; ?>
