<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Khách hàng</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/customers" class="row gx-2 gy-2 align-items-center">
            <div class="col-sm-8 col-md-6">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            <?php if (!empty($search)): ?>
                <div class="col-sm-auto">
                    <a href="<?= BASE_URL ?>/admin/customers" class="btn btn-outline-secondary">Xóa tìm kiếm</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No customers found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td class="ps-3"><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                                <td>
                                    <?php if ($c['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #ef4444;">Banned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php
                                            $isSelf = (Session::get('user')['id'] ?? 0) == $c['id'];
                                            $isAdmin = $c['role'] === 'admin';
                                            $canBan = !$isSelf && !$isAdmin;
                                        ?>
                                        <?php if ($canBan): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/customers/<?= $c['id'] ?>/ban" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                                                <?php if ($c['status'] === 'active'): ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="color: #ef4444; border-color: #ef4444;" title="Ban">
                                                        Ban
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm" style="color: #16a34a; border-color: #16a34a;" title="Unban">
                                                        Unban
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot change status">
                                                <?= $c['status'] === 'active' ? 'Ban' : 'Unban' ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="<?= BASE_URL ?>/admin/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($lastPage > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/customers?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
