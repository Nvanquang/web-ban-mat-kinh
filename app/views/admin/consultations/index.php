<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Consultations</h2>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
         <form method="GET" action="<?= BASE_URL ?>/admin/consultations" class="d-flex gap-3 align-items-center mb-0">
            <span class="fw-bold">Filter:</span>
            
            <div class="form-check">
                <input class="form-check-input" type="radio" name="status" id="status_all" value="all" <?= $currentStatus === 'all' ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label" for="status_all">All</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="status" id="status_pending" value="pending" <?= $currentStatus === 'pending' ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label text-warning fw-bold" for="status_pending">Pending</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="status" id="status_resolved" value="resolved" <?= $currentStatus === 'resolved' ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label text-success fw-bold" for="status_resolved">Resolved</label>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php if (empty($consultations)): ?>
                <li class="list-group-item text-center text-muted py-5">No consultations found.</li>
            <?php else: ?>
                <?php foreach ($consultations as $c): ?>
                    <li class="list-group-item p-4">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <span class="fw-bold text-muted">#<?= $c['id'] ?></span>
                                <span class="mx-2">•</span>
                                <span class="text-muted"><?= date('M d, Y', strtotime($c['sent_at'])) ?></span>
                                <span class="mx-2">•</span>
                                <strong><?= htmlspecialchars($c['customer_name'] ?? 'Khách lẻ') ?></strong>
                            </div>
                            <div>
                                <?php if ($c['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark me-2">Pending</span>
                                    <a href="<?= BASE_URL ?>/admin/consultations/<?= $c['id'] ?>" class="btn btn-sm btn-primary" style="background-color: #0ea5e9; border-color: #0ea5e9;">Reply</a>
                                <?php else: ?>
                                    <span class="badge bg-success me-2">Resolved</span>
                                    <a href="<?= BASE_URL ?>/admin/consultations/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <p class="mb-0 text-dark">
                            <?= nl2br(htmlspecialchars(mb_strimwidth($c['content'], 0, 150, '...'))) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    
    <?php if ($lastPage > 1): ?>
        <div class="card-footer bg-white">
            <nav class="mt-2">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/admin/consultations?status=<?= urlencode($currentStatus) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
