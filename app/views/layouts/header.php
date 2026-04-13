<?php
Session::init();
$cart_count = (int)($cart_count ?? 0);
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .top-bar {
            background-color: #1e293b;
            color: #f8fafc;
            font-size: 0.85rem;
            padding: 8px 0;
        }

        .navbar {
            transition: all 0.3s ease;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .navbar-brand {
            font-weight: 800;
            color: #0f172a !important;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 600;
            color: #475569 !important;
            margin: 0 5px;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #0ea5e9 !important;
        }

        .nav-icon-btn {
            position: relative;
            color: #334155;
            transition: transform 0.2s;
        }

        .nav-icon-btn:hover {
            color: #0ea5e9;
            transform: translateY(-2px);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            font-size: 0.65rem;
            padding: 3px 6px;
        }

        .search-box {
            border-radius: 20px;
            padding-left: 20px;
            background-color: #f1f5f9;
            border: 1px solid transparent;
        }

        .search-box:focus {
            background-color: #fff;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-clock me-1"></i> Mở cửa: 8:00 - 22:00 (Tất cả các ngày)
            </div>
            <div>
                <span class="me-3"><i class="bi bi-telephone text-info me-1"></i> Tổng đài: 1900 6789</span>
                <span><i class="bi bi-truck text-info me-1"></i> Miễn phí giao hàng toàn quốc</span>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                <i class="bi bi-eyeglasses text-primary" style="font-size: 2rem;"></i>
                <span>Eyeglass Shop</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === '' || basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>">Trang Chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === 'products' ? 'active' : '' ?>" href="<?= BASE_URL ?>/products">Cửa Hàng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/products?gender=male">Mắt Kính Nam</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/products?gender=female">Mắt Kính Nữ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/consultations">Hỗ trợ</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <form class="d-none d-lg-flex position-relative me-2" action="<?= BASE_URL ?>/products" method="GET">
                        <input class="form-control search-box" type="search" name="q" placeholder="Tìm kính..." aria-label="Search" style="width: 200px;">
                        <button class="btn border-0 position-absolute end-0 top-50 translate-middle-y text-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    <a href="<?= BASE_URL ?>/products" class="d-lg-none fs-5 nav-icon-btn"><i class="bi bi-search"></i></a>
                    <div class="nav-item dropdown">
                        <a class="nav-icon-btn text-decoration-none d-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4"></i>
                            <?php if (Session::isLoggedIn()): ?>
                                <span class="d-none d-md-block fw-semibold text-dark" style="font-size: 0.9rem;">
                                    <?= htmlspecialchars(Session::get('user')['username'] ?? 'Tài khoản') ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                            <?php if (Session::isLoggedIn()): ?>
                                <li>
                                    <h6 class="dropdown-header text-primary"><i class="bi bi-emoji-smile me-1"></i> Xin chào!</h6>
                                </li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/profile"><i class="bi bi-person me-2"></i> Hồ sơ cá nhân</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/orders"><i class="bi bi-box-seam me-2"></i> Đơn hàng của tôi</a></li>
                                <?php if (Session::isAdmin()): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item py-2 text-primary fw-bold" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2 me-2"></i> Bảng quản trị</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a></li>
                            <?php else: ?>
                                <li>
                                    <h6 class="dropdown-header">Khách hàng mới?</h6>
                                </li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/auth/login"><i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/auth/register"><i class="bi bi-person-plus me-2"></i> Đăng ký tài khoản</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php if (Session::isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/cart" class="nav-icon-btn text-decoration-none fs-4 position-relative border-start ps-3 ms-1">
                            <i class="bi bi-handbag"></i>
                            <span
                                id="cartBadge"
                                class="position-absolute badge rounded-pill bg-danger cart-badge shadow-sm <?= $cart_count > 0 ? '' : 'd-none' ?>"
                            >
                                <?= $cart_count ?>
                            </span>
                        </a>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </nav>

    <?php $flash = Session::flash(); if ($flash): ?>
        <div class="container mt-3">
            <div class="alert js-auto-dismiss alert-<?= ($flash['type'] ?? '') === 'error' ? 'danger' : htmlspecialchars((string)($flash['type'] ?? 'info'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars((string)($flash['message'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>