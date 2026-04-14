<?php
$badges = [
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-info text-white',
    'shipped' => 'bg-purple text-white',
    'completed' => 'bg-success',
    'cancelled' => 'bg-danger'
];
$statusColors = [
    'shipped' => 'background-color: #a855f7 !important;',
];
$validNextStates = [];
$current = $order['status'];
if ($current === 'pending') $validNextStates = ['confirmed', 'cancelled'];
elseif ($current === 'confirmed') $validNextStates = ['shipped', 'cancelled'];
elseif ($current === 'shipped') $validNextStates = ['completed', 'cancelled'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Order #<?= $order['id'] ?></h2>
    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="row">
    <!-- Customer / Shipping Info -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">CUSTOMER INFO & SHIPPING INFO</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold">Account Information</h6>
                        <?php if ($order['customer_id']): ?>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['account_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($order['username']) ?></p>
                        <?php else: ?>
                            <p class="mb-0">Guest</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold">Shipping Address</h6>
                        <p class="mb-1"><strong>To:</strong> <?= htmlspecialchars($order['receiver_name']) ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($order['receiver_phone']) ?></p>
                        <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                        <?php if (!empty($order['note'])): ?>
                            <p class="mb-0"><strong>Note:</strong> <?= nl2br(htmlspecialchars($order['note'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0">ITEMS</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= BASE_URL . '/' . ltrim($item['image_url'], '/') ?>" alt="" class="rounded" width="50" height="50" style="object-fit:cover">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px"><i class="bi bi-image text-muted"></i></div>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($item['product_name'] ?? 'Product Removed') ?></span>
                                    </div>
                                </td>
                                <td class="text-end"><?= number_format($item['sale_price'], 0, ',', '.') ?>₫</td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end fw-bold"><?= number_format($item['sale_price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="text-end text-danger fw-bold fs-5"><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update Status -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0">UPDATE STATUS</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="fw-bold d-block mb-2">Current:</span>
                    <span class="badge <?= $badges[$current] ?? 'bg-secondary' ?> fs-6" style="<?= $statusColors[$current] ?? '' ?>">
                        <?= ucfirst($current) ?>
                    </span>
                </div>

                <?php if (!empty($validNextStates)): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>/status">
                        <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Change to:</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">-- Change status --</option>
                                <?php foreach ($validNextStates as $st): ?>
                                    <option value="<?= $st ?>"><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn w-100 text-white" style="background-color: #0ea5e9;">Update Status</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0 text-center">
                        No further status updates possible.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
