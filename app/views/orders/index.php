<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php $orders = $orders ?? []; ?>

<div class="container py-4">
    <h2 class="fw-bold mb-3">My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted fs-5 mb-2">You have no orders yet.</div>
                <a class="btn btn-primary" href="<?= BASE_URL ?>/products">Browse Products</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:140px;">Order</th>
                            <th style="width:220px;">Date</th>
                            <th style="width:200px;">Total</th>
                            <th style="width:160px;">Status</th>
                            <th style="width:120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <?php
                            $status = (string)($o['status'] ?? 'pending');
                            $badge = match ($status) {
                                'pending'   => ['#fef3c7', '#d97706'],
                                'confirmed' => ['#dbeafe', '#2563eb'],
                                'shipped'   => ['#ede9fe', '#7c3aed'],
                                'completed' => ['#dcfce7', '#16a34a'],
                                'cancelled' => ['#fee2e2', '#dc2626'],
                                default     => ['#e5e7eb', '#374151'],
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold">#<?= (int)$o['id'] ?></td>
                                <td class="text-muted"><?= htmlspecialchars((string)$o['order_date'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                                <td class="fw-bold" style="color:#0ea5e9;"><?= number_format((float)$o['total_amount'], 0, ',', '.') ?> ₫</td>
                                <td>
                                    <span class="badge" style="background:<?= $badge[0] ?>; color:<?= $badge[1] ?>;">
                                        <?= htmlspecialchars($status, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/orders/<?= (int)$o['id'] ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

