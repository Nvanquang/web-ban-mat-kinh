<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customer Profile</h2>
    <a href="<?= BASE_URL ?>/admin/customers" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Customers
    </a>
</div>

<div class="row">
    <!-- Profile Info -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Profile Info</h5>
                <?php if ($customer['status'] === 'active'): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge" style="background-color: #ef4444;">Banned</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person text-secondary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="mb-0"><?= htmlspecialchars($customer['full_name'] ?? 'N/A') ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($customer['email']) ?></p>
                </div>
                
                <hr>
                
                <p class="mb-2"><strong>Username:</strong> <?= htmlspecialchars($customer['username']) ?></p>
                <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></p>
                <p class="mb-2"><strong>Role:</strong> <?= htmlspecialchars(ucfirst($customer['role'])) ?></p>
                <p class="mb-2"><strong>Joined:</strong> <?= date('M d, Y', strtotime($customer['created_at'])) ?></p>
                
                <hr>
                
                <?php
                    $isSelf = (Session::get('user')['id'] ?? 0) == $customer['id'];
                    $isAdmin = $customer['role'] === 'admin';
                    $canBan = !$isSelf && !$isAdmin;
                ?>
                <?php if ($canBan): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/customers/<?= $customer['id'] ?>/ban" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                        <?php if ($customer['status'] === 'active'): ?>
                            <button type="submit" class="btn w-100" style="background-color: #ef4444; color: white;">Ban User</button>
                        <?php else: ?>
                            <button type="submit" class="btn w-100" style="background-color: #16a34a; color: white;">Unban User</button>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0 text-center">
                        Cannot change status for this account.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order History -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No orders found for this customer.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="ps-3"><?= $order['id'] ?></td>
                                        <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
                                        <td>
                                            <?php
                                                $badges = [
                                                    'pending' => 'bg-warning text-dark',
                                                    'confirmed' => 'bg-info text-white',
                                                    'shipped' => 'bg-primary',
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger'
                                                ];
                                                $badgeClass = $badges[$order['status']] ?? 'bg-secondary';
                                                $style = $order['status'] === 'shipped' ? 'background-color: #a855f7 !important;' : '';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>" style="<?= $style ?>"><?= ucfirst($order['status']) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View Order</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
