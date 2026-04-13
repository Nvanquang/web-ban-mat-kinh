<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php
$cart = $cart ?? [];
$subtotal = (float)($subtotal ?? 0);
$shipping = (float)($shipping ?? 0);
$total = (float)($total ?? 0);
$customer = $customer ?? [];
$oldInput = $oldInput ?? [];
$payment = $oldInput['payment_method'] ?? 'COD';
?>

<div class="container py-4">
    <h2 class="fw-bold mb-3">Checkout</h2>

    <form method="POST" action="<?= BASE_URL ?>/orders/checkout">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="fw-bold mb-3">SHIPPING INFORMATION</div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Receiver Name <span class="text-danger">*</span></label>
                            <input type="text" name="receiver_name" class="form-control"
                                   value="<?= htmlspecialchars((string)($oldInput['receiver_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="receiver_phone" class="form-control"
                                   value="<?= htmlspecialchars((string)($oldInput['receiver_phone'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Shipping Address <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control" rows="3" required><?= htmlspecialchars((string)($oldInput['shipping_address'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Note (optional)</label>
                            <textarea name="note" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars((string)($oldInput['note'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></textarea>
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="useMyAddress()">
                            Use my account address
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-summary">
                    <div class="card-body">
                        <div class="fw-bold mb-3">ORDER REVIEW</div>
                        <?php foreach ($cart as $it): ?>
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <div class="text-truncate">
                                    <?= htmlspecialchars((string)$it['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                    <span class="text-muted">× <?= (int)$it['quantity'] ?></span>
                                </div>
                                <div class="fw-semibold">
                                    <?= number_format(((float)$it['price']) * ((int)$it['quantity']), 0, ',', '.') ?> ₫
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <div class="text-muted">Subtotal</div>
                            <div class="fw-semibold"><?= number_format($subtotal, 0, ',', '.') ?> ₫</div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Shipping</div>
                            <div class="fw-semibold"><?= $shipping <= 0 ? 'Free' : number_format($shipping, 0, ',', '.') . ' ₫' ?></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted">Total</div>
                            <div class="fw-bold" style="color:#0ea5e9; font-size:1.4rem;">
                                <?= number_format($total, 0, ',', '.') ?> ₫
                            </div>
                        </div>

                        <div class="fw-bold mb-2">Payment Method</div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="COD" <?= $payment === 'COD' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pm_cod">COD (Cash on Delivery)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_bank" value="Bank Transfer" <?= $payment === 'Bank Transfer' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pm_bank">Bank Transfer</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_vnpay" value="VNPay" <?= $payment === 'VNPay' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pm_vnpay">VNPay</label>
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2" style="background:#0ea5e9; font-size:1.05rem;">
                            Place Order
                        </button>
                        <a class="d-block text-center mt-3 text-decoration-none" href="<?= BASE_URL ?>/cart">Back to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function useMyAddress() {
        const name = <?= json_encode((string)($customer['full_name'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
        const phone = <?= json_encode((string)($customer['phone'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
        const address = <?= json_encode((string)($customer['address'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;

        if (name) document.querySelector('input[name="receiver_name"]').value = name;
        if (phone) document.querySelector('input[name="receiver_phone"]').value = phone;
        if (address) document.querySelector('textarea[name="shipping_address"]').value = address;
    }
</script>

<style>
    .sticky-summary{ position: sticky; top: 90px; }
    .btn[style*="#0ea5e9"]:hover{ background:#0284c7 !important; }
</style>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

