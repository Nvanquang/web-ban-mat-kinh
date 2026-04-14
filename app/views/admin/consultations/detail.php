<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Consultation #<?= $consultation['id'] ?></h2>
    <a href="<?= BASE_URL ?>/admin/consultations" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Consultations
    </a>
</div>

<div class="row">
    <div class="col-md-9 mx-auto">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">From: <?= htmlspecialchars($consultation['customer_name'] ?? 'Guest') ?> (<?= htmlspecialchars($consultation['customer_email'] ?? 'N/A') ?>)</h5>
                    <small class="text-muted">Sent: <?= date('M d, Y H:i', strtotime($consultation['sent_at'])) ?></small>
                </div>
                <div>
                    <?php if ($consultation['status'] === 'pending'): ?>
                        <span class="badge bg-warning text-dark fs-6">Pending</span>
                    <?php else: ?>
                        <span class="badge bg-success fs-6">Resolved</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-3">Question:</h6>
                <div class="p-4 bg-light rounded border mb-4">
                    <?= nl2br(htmlspecialchars($consultation['content'])) ?>
                </div>
                
                <h6 class="fw-bold mb-3">Your Reply <?= $consultation['status'] === 'pending' ? '<span class="text-danger">*</span>' : '' ?></h6>
                
                <form method="POST" action="<?= BASE_URL ?>/admin/consultations/<?= $consultation['id'] ?>/reply">
                    <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
                    <div class="mb-3">
                        <?php 
                        $oldInput = Session::getOldInput();
                        $oldReply = $oldInput['reply'] ?? $consultation['reply'] ?? ''; 
                        ?>
                        <textarea name="reply" rows="6" class="form-control" placeholder="Write your reply here..." required><?= htmlspecialchars($oldReply) ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/admin/consultations" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn text-white" style="background-color: #0ea5e9;">
                            <?= $consultation['status'] === 'pending' ? 'Send Reply & Mark Resolved' : 'Update Reply' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
