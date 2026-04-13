<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php
$order = $order ?? [];
$items = $order['items'] ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/orders">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?= (int)($order['id'] ?? 0) ?></li>
        </ol>
    </nav>

    <?php
    $status = (string)($order['status'] ?? 'pending');
    $badge = match ($status) {
        'pending'   => ['#fef3c7', '#d97706'],
        'confirmed' => ['#dbeafe', '#2563eb'],
        'shipped'   => ['#ede9fe', '#7c3aed'],
        'completed' => ['#dcfce7', '#16a34a'],
        'cancelled' => ['#fee2e2', '#dc2626'],
        default     => ['#e5e7eb', '#374151'],
    };
    ?>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Order #<?= (int)($order['id'] ?? 0) ?></h2>
            <div class="text-muted">Placed: <?= htmlspecialchars((string)($order['order_date'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
        </div>
        <div>
            <span class="badge" style="background:<?= $badge[0] ?>; color:<?= $badge[1] ?>;">
                <?= htmlspecialchars($status, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="fw-bold mb-3">ITEMS</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;"></th>
                            <th>Product</th>
                            <th style="width:160px;">Price</th>
                            <th style="width:120px;">Qty</th>
                            <th style="width:180px;" class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <?php
                            $rawImg = (string)($it['image_url'] ?? '');
                            if ($rawImg === '') {
                                $imgSrc = BASE_URL . '/public/uploads/no-image.jpg';
                            } elseif (preg_match('~^https?://~i', $rawImg)) {
                                $imgSrc = $rawImg;
                            } elseif (str_starts_with($rawImg, 'public/uploads/')) {
                                $imgSrc = BASE_URL . '/' . $rawImg;
                            } else {
                                $imgSrc = BASE_URL . '/public/uploads/' . ltrim($rawImg, '/');
                            }
                            $qty = (int)($it['quantity'] ?? 0);
                            $price = (float)($it['sale_price'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" class="rounded border" style="width:60px;height:60px;object-fit:cover;" alt="">
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars((string)($it['product_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                                <td><?= number_format($price, 0, ',', '.') ?> ₫</td>
                                <td><?= $qty ?></td>
                                <td class="text-end fw-bold" style="color:#0ea5e9;"><?= number_format($price * $qty, 0, ',', '.') ?> ₫</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-bold mb-3">SHIPPING</div>
                    <div class="mb-1 fw-semibold"><?= htmlspecialchars((string)($order['receiver_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                    <div class="text-muted mb-2"><?= htmlspecialchars((string)($order['receiver_phone'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                    <div><?= nl2br(htmlspecialchars((string)($order['shipping_address'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></div>
                    <?php if (!empty($order['note'])): ?>
                        <hr>
                        <div class="text-muted small">Note</div>
                        <div><?= nl2br(htmlspecialchars((string)$order['note'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-bold mb-3">PAYMENT</div>
                    <div class="mb-2"><span class="text-muted">Method:</span> <?= htmlspecialchars((string)($order['payment_method'] ?? 'COD'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                    <div><span class="text-muted">Total:</span> <span class="fw-bold" style="color:#0ea5e9;"><?= number_format((float)($order['total_amount'] ?? 0), 0, ',', '.') ?> ₫</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

