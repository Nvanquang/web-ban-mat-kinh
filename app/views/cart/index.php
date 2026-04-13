<?php require_once APPROOT . '/app/views/layouts/header.php'; ?>
<?php
$cart = $cart ?? [];
$subtotal = (float)($subtotal ?? 0);
$shipping = (float)($shipping ?? 0);
$total = (float)($total ?? 0);
$itemCount = 0;
foreach ($cart as $it) {
    $itemCount += (int)($it['quantity'] ?? 0);
}
?>

<div class="container py-4">
    <?php if (empty($cart)): ?>
        <div class="text-center py-5">
            <div class="fs-1 mb-3">🛒</div>
            <h3 class="fw-bold mb-2">Your cart is empty</h3>
            <p class="text-muted mb-4">Start shopping to add items</p>
            <a class="btn text-white px-4 py-2" style="background:#0ea5e9;" href="<?= BASE_URL ?>/products">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <h2 class="fw-bold mb-0">Shopping Cart</h2>
                <div class="text-muted"><span id="cartItemCount"><?= $itemCount ?></span> items</div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <?php foreach ($cart as $productId => $item): ?>
                            <?php
                            $qty = max(1, (int)($item['quantity'] ?? 1));
                            $rowTotal = ((float)($item['price'] ?? 0)) * $qty;

                            $rawImg = (string)($item['image_url'] ?? '');
                            if ($rawImg === '') {
                                $imgSrc = BASE_URL . '/public/uploads/no-image.jpg';
                            } elseif (preg_match('~^https?://~i', $rawImg)) {
                                $imgSrc = $rawImg;
                            } elseif (str_starts_with($rawImg, 'public/uploads/')) {
                                $imgSrc = BASE_URL . '/' . $rawImg;
                            } else {
                                $imgSrc = BASE_URL . '/public/uploads/' . ltrim($rawImg, '/');
                            }
                            ?>

                            <div class="cart-row d-flex align-items-center gap-3" id="cartRow_<?= (int)$productId ?>">
                                <img
                                    src="<?= htmlspecialchars($imgSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars((string)($item['product_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                    class="cart-thumb"
                                >

                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars((string)($item['product_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= number_format((float)($item['price'] ?? 0), 0, ',', '.') ?> ₫
                                    </div>
                                    <div class="mt-2 d-flex align-items-center gap-3 flex-nowrap">
                                        <form method="POST" action="<?= BASE_URL ?>/cart/update" data-ajax-cart="1" data-ajax-cart-mode="update" data-ajax-action="<?= BASE_URL ?>/cart/updateAjax" class="d-flex align-items-center gap-2 flex-nowrap">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                            <input type="hidden" name="product_id" value="<?= (int)$productId ?>">
                                            <div class="input-group input-group-sm qty-control flex-nowrap" style="width: 140px;">
                                                <button type="button" class="btn btn-light border" onclick="qtyStepSubmit('qty_<?= (int)$productId ?>', -1, this.form)">−</button>
                                                <input id="qty_<?= (int)$productId ?>" type="number" min="1" name="quantity" class="form-control text-center border" value="<?= $qty ?>">
                                                <button type="button" class="btn btn-light border" onclick="qtyStepSubmit('qty_<?= (int)$productId ?>', 1, this.form)">+</button>
                                            </div>
                                        </form>

                                        <form method="POST" action="<?= BASE_URL ?>/cart/remove" data-ajax-cart="1" data-ajax-cart-mode="remove" data-ajax-action="<?= BASE_URL ?>/cart/removeAjax">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                            <input type="hidden" name="product_id" value="<?= (int)$productId ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 remove-link">Remove</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <div class="fw-bold" style="color:#0ea5e9;">
                                        <span id="cartRowTotal_<?= (int)$productId ?>"><?= number_format($rowTotal, 0, ',', '.') ?></span> ₫
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-3">
                            <form method="POST" action="<?= BASE_URL ?>/cart/clear">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-link text-danger p-0">Clear Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-summary">
                    <div class="card-body">
                        <div class="fw-bold mb-3">ORDER SUMMARY</div>
                        <div class="d-flex justify-content-between mb-2">
                            <div class="text-muted">Subtotal</div>
                            <div class="fw-semibold"><span id="cartSubtotal"><?= number_format($subtotal, 0, ',', '.') ?></span> ₫</div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Shipping</div>
                            <div class="fw-semibold"><span id="cartShipping"><?= $shipping <= 0 ? 'Free' : number_format($shipping, 0, ',', '.') . ' ₫' ?></span></div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted">Total</div>
                            <div class="fw-bold" style="color:#0ea5e9; font-size:1.4rem;">
                                <span id="cartTotal"><?= number_format($total, 0, ',', '.') ?></span> ₫
                            </div>
                        </div>

                        <a class="btn text-white w-100 py-2" style="background:#0ea5e9;" href="<?= BASE_URL ?>/orders/checkout">
                            Proceed to Checkout
                        </a>
                        <a class="d-block text-center mt-3 text-decoration-none" href="<?= BASE_URL ?>/products">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function qtyStepSubmit(id, delta, form) {
        const el = document.getElementById(id);
        if (!el) return;
        const current = parseInt(el.value || '1', 10);
        const next = Math.max(1, current + delta);
        el.value = String(next);
        if (form && typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else if (form) {
            form.submit();
        }
    }
</script>

<style>
    .cart-row{
        border-bottom: 1px solid rgba(15, 23, 42, .10);
        padding: 1rem 0;
    }
    .cart-row:last-child{ border-bottom: none; }
    .cart-thumb{
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: .5rem;
        border: 1px solid rgba(15, 23, 42, .08);
        background: #f8fafc;
    }
    .remove-link{ text-decoration: none; }
    .remove-link:hover{ text-decoration: underline; }
    .qty-control .btn{ width: 34px; }
    .qty-control input::-webkit-outer-spin-button,
    .qty-control input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-control input[type=number] { appearance: textfield; -moz-appearance: textfield; }
    .sticky-summary{ position: sticky; top: 90px; }
    .btn[style*="#0ea5e9"]:hover{ background:#0284c7 !important; }
</style>

<?php require_once APPROOT . '/app/views/layouts/footer.php'; ?>

