<footer class="bg-dark text-white py-5 mt-auto border-top border-secondary border-opacity-25">
    <div class="container">
        <div class="row g-5">
            <!-- Cột 1: Giới thiệu chung -->
            <div class="col-lg-4 col-md-6">
                <a href="index.php" class="text-white text-decoration-none d-block mb-3 fs-3 fw-bold">👓 Eyeglass Online</a>
                <p class="text-secondary mb-4" style="line-height: 1.8;">Chuyên cung cấp các sản phẩm kính mắt thời trang, kính cận và phụ kiện bảo vệ mắt cao cấp. Tôn vinh phong cách và bảo vệ cửa sổ tâm hồn của bạn.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-outline-secondary rounded-circle border-0 text-white"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="btn btn-outline-secondary rounded-circle border-0 text-white"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="btn btn-outline-secondary rounded-circle border-0 text-white"><i class="bi bi-tiktok fs-5"></i></a>
                </div>
            </div>

            <!-- Cột 2: Cột trống (tạo khoảng cách) hoặc liên kết nhanh -->
            <div class="col-lg-3 col-md-6 offset-lg-1">
                <h5 class="text-white fw-bold mb-4">Về Chúng Tôi</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 text-secondary">
                    <li><a href="shop.php" class="text-decoration-none text-secondary">Sản Phẩm Mới</a></li>
                    <li><a href="#" class="text-decoration-none text-secondary">Hệ Thống Cửa Hàng</a></li>
                    <li><a href="#" class="text-decoration-none text-secondary">Chính Sách Bảo Mật</a></li>
                </ul>
            </div>

            <!-- Cột 3: Thông tin liên hệ  -->
            <div class="col-lg-4 col-md-12">
                <h5 class="text-white fw-bold mb-4">Liên hệ</h5>
                <ul class="list-unstyled d-flex flex-column gap-4 text-secondary">
                    <li class="d-flex align-items-start">
                        <i class="bi bi-geo-alt fs-4 me-3 text-white opacity-75 mt-1"></i>
                        <span style="line-height: 1.6; font-size: 1.05rem;">123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="bi bi-telephone fs-4 me-3 text-white opacity-75"></i>
                        <span style="font-size: 1.05rem;">1900 6789</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="bi bi-envelope fs-4 me-3 text-white opacity-75"></i>
                        <span style="font-size: 1.05rem;">hello@eyeglass.vn</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <div class="text-center text-secondary" style="font-size: 0.9rem;">
            <p class="mb-0">&copy; 2026 Eyeglass Online . All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
    /* Custom hover màu tự động cho footer */
    footer a.text-secondary:hover {
        color: #fff !important;
        padding-left: 5px;
        transition: all 0.3s ease;
    }

    footer a.text-secondary {
        transition: all 0.3s ease;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (file_exists('assets/js/main.js')): ?>
    <script src="assets/js/main.js"></script>
<?php endif; ?>
<script>
    // Auto hide flash alerts after a short delay (SSR friendly, no AJAX)
    window.addEventListener('load', function () {
        const alerts = document.querySelectorAll('.alert.js-auto-dismiss');
        if (!alerts.length) return;
        setTimeout(function () {
            alerts.forEach(function (el) {
                try {
                    const instance = bootstrap.Alert.getOrCreateInstance(el);
                    instance.close();
                } catch (e) {
                    el.remove();
                }
            });
        }, 2500);
    });
</script>
<script>
    // AJAX Add-to-cart (no reload). Progressive enhancement: forms still work without JS.
    window.addEventListener('load', function () {
        const forms = document.querySelectorAll('form[data-ajax-cart="1"]');
        if (!forms.length) return;

        const showToast = function (message, type) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3 px-3 py-2 rounded-3 shadow text-white';
            toast.style.zIndex = '2000';
            toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
            toast.textContent = message || '';
            document.body.appendChild(toast);
            setTimeout(function () { toast.remove(); }, 2000);
        };

        const setBadge = function (count) {
            const el = document.getElementById('cartBadge');
            if (!el) return;
            const c = parseInt(count || '0', 10);
            if (c > 0) {
                el.textContent = String(c);
                el.classList.remove('d-none');
            } else {
                el.textContent = '0';
                el.classList.add('d-none');
            }
        };

        const formatVnd = function (n) {
            const num = Number(n || 0);
            try { return num.toLocaleString('vi-VN'); } catch (e) { return String(Math.round(num)); }
        };

        forms.forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                try {
                    const fd = new FormData(form);
                    const res = await fetch(form.getAttribute('data-ajax-action') || form.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    });
                    const data = await res.json();
                    if (!data || data.success !== true) {
                        showToast((data && data.message) ? data.message : 'Add to cart failed.', 'error');
                        return;
                    }
                    setBadge(data.cart_count || 0);
                    showToast(data.message || 'Added to cart!', 'success');

                    // Cart page: remove mode updates UI without reload
                    if (form.getAttribute('data-ajax-cart-mode') === 'remove') {
                        const fd = new FormData(form);
                        const pid = fd.get('product_id');
                        if (pid) {
                            const row = document.getElementById('cartRow_' + pid);
                            if (row) row.remove();
                        }

                        const itemCountEl = document.getElementById('cartItemCount');
                        if (itemCountEl && typeof data.cart_count !== 'undefined') itemCountEl.textContent = String(data.cart_count);

                        const subtotalEl = document.getElementById('cartSubtotal');
                        if (subtotalEl && typeof data.subtotal !== 'undefined') subtotalEl.textContent = formatVnd(data.subtotal);

                        const shippingEl = document.getElementById('cartShipping');
                        if (shippingEl && typeof data.shipping !== 'undefined') {
                            shippingEl.textContent = (Number(data.shipping) <= 0) ? 'Free' : (formatVnd(data.shipping) + ' ₫');
                        }

                        const totalEl = document.getElementById('cartTotal');
                        if (totalEl && typeof data.total !== 'undefined') totalEl.textContent = formatVnd(data.total);

                        if (data.empty === true) {
                            // easiest: reload cart to show empty state layout
                            window.location.href = '<?= BASE_URL ?>/cart';
                        }
                    }

                    // Cart page: update qty mode updates totals without reload
                    if (form.getAttribute('data-ajax-cart-mode') === 'update') {
                        const pid = data.product_id;
                        if (pid) {
                            const rowTotalEl = document.getElementById('cartRowTotal_' + pid);
                            if (rowTotalEl && typeof data.row_total !== 'undefined') rowTotalEl.textContent = formatVnd(data.row_total);
                        }

                        const itemCountEl = document.getElementById('cartItemCount');
                        if (itemCountEl && typeof data.cart_count !== 'undefined') itemCountEl.textContent = String(data.cart_count);

                        const subtotalEl = document.getElementById('cartSubtotal');
                        if (subtotalEl && typeof data.subtotal !== 'undefined') subtotalEl.textContent = formatVnd(data.subtotal);

                        const shippingEl = document.getElementById('cartShipping');
                        if (shippingEl && typeof data.shipping !== 'undefined') {
                            shippingEl.textContent = (Number(data.shipping) <= 0) ? 'Free' : (formatVnd(data.shipping) + ' ₫');
                        }

                        const totalEl = document.getElementById('cartTotal');
                        if (totalEl && typeof data.total !== 'undefined') totalEl.textContent = formatVnd(data.total);
                    }
                } catch (err) {
                    showToast('Add to cart failed.', 'error');
                }
            });
        });
    });
</script>
</body>

</html>