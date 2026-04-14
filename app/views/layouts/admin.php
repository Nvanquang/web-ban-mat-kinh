<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?> — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
    
</head>
<body class="admin-layout">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <button class="btn btn-link text-white me-3 d-lg-none" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/admin">
                <i class="bi bi-eyeglasses me-2"></i>
                <span class="d-none d-sm-inline">EyeGlass Admin</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['full_name'] ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar bg-white border-end shadow-sm" id="sidebar">
            <div class="sidebar-header p-3 border-bottom">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Menu
                </h6>
            </div>
            <div class="sidebar-nav p-2">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin" class="nav-link rounded <?= $currentPage === 'dashboard' ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="bi bi-speedometer2 me-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/products" class="nav-link rounded <?= $currentPage === 'products' ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="bi bi-box-seam me-3"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/categories" class="nav-link rounded <?= $currentPage === 'categories' ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="bi bi-tags me-3"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/orders" class="nav-link rounded <?= $currentPage === 'orders' ? 'active bg-secondary text-white' : 'text-muted' ?>" >
                            <i class="bi bi-receipt me-3"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/customers" class="nav-link rounded <?= $currentPage === 'customers' ? 'active bg-secondary text-white' : 'text-muted' ?>">
                            <i class="bi bi-people me-3"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/consultations" class="nav-link rounded <?= $currentPage === 'consultations' ? 'active bg-secondary text-white' : 'text-muted' ?>">
                            <i class="bi bi-chat-dots me-3"></i>
                            <span>Consultations</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1 bg-light">
            <div class="container-fluid p-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/admin" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <?php if ($currentPage !== 'dashboard'): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= ucfirst($currentPage) ?>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>

                <!-- Flash Messages -->
                <?php $flash = Session::flash(); ?>

                <!-- Page Content -->
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    <script>
        // Define BASE_URL for JavaScript
        window.BASE_URL = '<?= BASE_URL ?>';
        
        <?php if ($flash): ?>
        $(document).ready(function() {
            const type = '<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'success' ? 'success' : 'info') ?>';
            const message = <?= json_encode($flash['message']) ?>;
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>