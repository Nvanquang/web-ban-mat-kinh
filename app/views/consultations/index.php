<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php $consultations = $consultations ?? []; ?>

<div class="container py-4">
    <h2 class="fw-bold mb-3">Consultation &amp; Support</h2>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="fw-bold mb-2">Ask a Question</div>

            <form method="POST" action="<?= BASE_URL ?>/consultations/send">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">

                <div class="mb-2">
                    <label class="form-label fw-semibold">Your Question <span class="text-danger">*</span></label>
                    <textarea
                        id="questionContent"
                        name="content"
                        class="form-control"
                        rows="5"
                        maxlength="2000"
                        style="min-height: 120px; resize: vertical;"
                        placeholder="Type your question about glasses, prescriptions, sizing, or anything else..."
                        required
                    ></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small"><span id="charCount">0</span> / 2000 characters</div>
                    <button type="submit" class="btn text-white" style="background:#0ea5e9;">Submit Question</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-2">
        <div class="fw-bold">My Questions (<?= count($consultations) ?>)</div>
    </div>

    <?php if (empty($consultations)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted fs-5">You haven't asked any questions yet.</div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php foreach ($consultations as $c): ?>
                    <?php
                    $status = (string)($c['status'] ?? 'pending');
                    $badge = $status === 'resolved'
                        ? ['#dcfce7', '#16a34a']
                        : ['#fef3c7', '#d97706'];
                    $date = (string)($c['sent_at'] ?? '');
                    ?>
                    <div class="question-item py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small">
                                <?= htmlspecialchars($date, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                            </div>
                            <span class="badge" style="background:<?= $badge[0] ?>; color:<?= $badge[1] ?>;">
                                <?= htmlspecialchars($status, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="fw-semibold mb-2">
                            “<?= htmlspecialchars((string)($c['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>”
                        </div>

                        <?php if ($status === 'resolved' && !empty($c['reply'])): ?>
                            <div class="reply-block">
                                <div class="fw-bold mb-1">💬 Admin Reply:</div>
                                <div><?= nl2br(htmlspecialchars((string)$c['reply'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted fst-italic">⏳ Awaiting response from our team...</div>
                        <?php endif; ?>
                    </div>
                    <hr class="my-0">
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    (function () {
        const el = document.getElementById('questionContent');
        const out = document.getElementById('charCount');
        if (!el || !out) return;
        const update = () => { out.textContent = String(el.value.length); };
        el.addEventListener('input', update);
        update();
    })();
</script>

<style>
    .reply-block{
        background: #f0f9ff;
        border-left: 4px solid #0ea5e9;
        padding: 1rem;
        border-radius: .5rem;
    }
    .question-item:last-child + hr{ display:none; }
    .btn[style*="#0ea5e9"]:hover{ background:#0284c7 !important; }
</style>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

