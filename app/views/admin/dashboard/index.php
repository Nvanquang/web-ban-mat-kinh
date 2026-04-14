<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon revenue me-3">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stats-value text-success">
                        <?= number_format($revenue, 0, ',', '.') ?> ₫
                    </div>
                    <div class="stats-label">Doanh thu tháng này</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon orders me-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stats-value text-warning">
                        <?= ($orderStats['pending'] ?? 0) + ($orderStats['confirmed'] ?? 0) + ($orderStats['shipped'] ?? 0) + ($orderStats['completed'] ?? 0) ?>
                    </div>
                    <div class="stats-label">Tổng đơn hàng</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon customers me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stats-value text-info">
                        <?= $activeCustomers ?>
                    </div>
                    <div class="stats-label">Khách hàng hoạt động</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon products me-3">
                    <i class="bi bi-eyeglasses"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stats-value text-primary">
                        <?= $sellingProducts ?>
                    </div>
                    <div class="stats-label">Sản phẩm đang bán</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-receipt me-2 text-primary"></i>
                    <h5 class="mb-0">Đơn hàng gần đây</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="empty-state-title">Chưa có đơn hàng</div>
                        <div class="empty-state-text">Đơn hàng sẽ xuất hiện khi khách hàng bắt đầu đặt hàng.</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="me-2">#<?= $order['id'] ?></strong>
                                            <span class="text-muted">
                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($order['full_name']) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-primary me-3">
                                                <?= number_format($order['total_amount'], 0, ',', '.') ?> ₫
                                            </span>
                                            <span class="badge bg-<?= match($order['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'shipped' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            } ?> fs-6">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                                <?= match ($order['status']) {
                            'pending' => 'Đang chờ',
                            'confirmed' => 'Đã xác nhận',
                            'shipped' => 'Đã giao',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy',
                            default => ucfirst($order['status'])
                        } ?>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('M j, H:i', strtotime($order['order_date'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>Xem tất cả đơn hàng
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-trophy me-2 text-warning"></i>
                    <h5 class="mb-0">Sản phẩm bán chạy</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($topProducts)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="empty-state-title">Chưa có dữ liệu bán hàng</div>
                        <div class="empty-state-text">Sản phẩm bán chạy sẽ xuất hiện khi có doanh số.</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topProducts as $index => $product): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="badge bg-warning text-dark fs-5 rounded-circle d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;">
                                            #<?= $index + 1 ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold mb-1">
                                            <i class="bi bi-eyeglasses me-2 text-primary"></i>
                                            <?= htmlspecialchars($product['product_name']) ?>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2">
                                                <i class="bi bi-graph-up me-1"></i>
                                                <?= $product['total_sold'] ?> bán
                                            </span>
                                            <small class="text-muted">
                                                <i class="bi bi-trending-up me-1"></i>
                                                Bán chạy
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($pendingConsultations)): ?>
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-chat-dots me-2 text-info"></i>
                        <h5 class="mb-0">Tư vấn chờ xử lý</h5>
                        <span class="badge bg-info ms-2 fs-6">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            <?= count($pendingConsultations) ?> mới
                        </span>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/consultations" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Xem tất cả
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($pendingConsultations as $consultation): ?>
                        <div class="col-md-6">
                            <div class="card border-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="bi bi-person-circle fs-4 text-info me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-person me-1"></i>
                                                <?= htmlspecialchars($consultation['full_name']) ?>
                                            </h6>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= date('M j, H:i', strtotime($consultation['sent_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0 text-dark">
                                            <i class="bi bi-chat-quote me-1 text-info"></i>
                                            <?= htmlspecialchars(substr($consultation['content'], 0, 150)) ?>
                                            <?= strlen($consultation['content']) > 150 ? '...' : '' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>