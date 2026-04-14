<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Đơn hàng</h2>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" action="<?= BASE_URL ?>/admin/orders" class="d-flex gap-2 align-items-center">
            <label for="status" class="form-label mb-0 fw-bold">Trạng thái:</label>
            <select name="status" id="status" class="form-select w-auto" onchange="this.form.submit()">
                <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>Tất cả</option>
                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="confirmed" <?= $currentStatus === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                <option value="shipped" <?= $currentStatus === 'shipped' ? 'selected' : '' ?>>Đang giao</option>
                <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Không tìm thấy đơn hàng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['full_name'] ?? 'Khách lẻ') ?></td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
                                <td>
                                    <?php
                                        $badges = [
                                            'pending' => 'bg-warning text-dark',
                                            'confirmed' => 'bg-info text-white',
                                            'shipped' => 'bg-primary' /* Note: shipped=purple but bootstrap primary is close, or use custom style */,
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger'
                                        ];
                                        $labels = [
                                            'pending' => 'Pending',
                                            'confirmed' => 'Confirmed',
                                            'shipped' => 'Shipped',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                        $badgeClass = $badges[$order['status']] ?? 'bg-secondary';
                                        $badgeStyle = $order['status'] === 'shipped' ? 'background-color: #a855f7 !important; color: white;' : '';
                                        $label = $labels[$order['status']] ?? ucfirst($order['status']);
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="<?= $badgeStyle ?>"><?= $label ?></span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($lastPage > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/orders?status=<?= urlencode($currentStatus) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
