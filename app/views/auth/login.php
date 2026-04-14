<?php
$oldInput = $oldInput ?? [];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? APP_NAME ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
<!-- Flash message -->
<?php $flash = Session::flash(); if ($flash): ?>
    <div class="container mt-3" style="max-width: 520px;">
        <div class="alert alert-<?= ($flash['type'] ?? '') === 'error' ? 'danger' : htmlspecialchars((string)($flash['type'] ?? 'info'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string)($flash['message'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>
<!-- End flash message -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12" style="max-width: 420px;">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-eyeglasses text-primary" style="font-size: 2rem;"></i>
                        <div class="fw-bold" style="font-size: 1.1rem;">EyeGlass Shop</div>
                    </div>
                    <div class="text-muted mb-4">Welcome back</div>

                    <form method="POST" action="<?= BASE_URL ?>/auth/login" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars((string)($oldInput['username'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                required
                                autocomplete="username"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2 fw-semibold" style="background:#0ea5e9;">
                            Login
                        </button>

                        <div class="text-center mt-3">
                            <span class="text-muted">Don't have an account?</span>
                            <a href="<?= BASE_URL ?>/auth/register" class="text-decoration-none fw-semibold" style="color:#0ea5e9;">Register here</a>
                        </div>
                    </form>
                </div>
            </div>
            <style>
                .form-control:focus {
                    border-color: #0ea5e9;
                    box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
                }
                .btn[style*="#0ea5e9"]:hover { background: #0284c7 !important; }
            </style>
        </div>
    </div>
</div>
</body>

