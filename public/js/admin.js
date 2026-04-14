// Admin Panel JavaScript Enhancements
$(document).ready(function() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').on('click', function() {
        const sidebar = $('#sidebar');
        const backdrop = $('<div class="sidebar-backdrop"></div>');

        if (sidebar.hasClass('show')) {
            sidebar.removeClass('show');
            $('.sidebar-backdrop').remove();
        } else {
            sidebar.addClass('show');
            $('body').append(backdrop);
            backdrop.addClass('show');

            backdrop.on('click', function() {
                sidebar.removeClass('show');
                backdrop.remove();
            });
        }
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            const sidebar = $('#sidebar');
            const toggle = $('#sidebarToggle');

            if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 &&
                !toggle.is(e.target) && toggle.has(e.target).length === 0) {
                sidebar.removeClass('show');
                $('.sidebar-backdrop').remove();
            }
        }
    });

    // Enhanced delete confirmation
    $(document).on('submit', 'form[action*="delete"]', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        
        if (!confirm('Xóa sản phẩm này?')) {
            // Reset button to original state if user cancels
            submitBtn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
            e.preventDefault();
            return false;
        }

        // Show loading state only if confirmed
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
    });

    // Form submission loading states (skip delete forms)
    $(document).on('submit', 'form:not([action*="delete"])', function() {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        if (submitBtn.length && !submitBtn.prop('disabled')) {
            submitBtn.prop('disabled', true).prepend('<span class="spinner-border spinner-border-sm me-2"></span>');
        }
    });

    // AJAX delete product
    $(document).on('submit', '.delete-product-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const productId = form.data('product-id');
        const productName = form.data('product-name');
        
        if (!confirm(`Xóa sản phẩm "${productName}"?`)) {
            return false;
        }

        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const currentRow = form.closest('tr');
                    const tableBody = currentRow.closest('tbody');
                    const remainingRows = tableBody.find('tr').length - 1; // trừ 1 vì row hiện tại sẽ bị xóa
                    
                    // Remove row from table
                    currentRow.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update pagination info if exists
                        const totalText = $('.text-muted small i.bi-info-circle').parent();
                        if (totalText.length) {
                            const currentText = totalText.text();
                            const match = currentText.match(/Hiển thị (\d+) trên tổng số (\d+) sản phẩm/);
                            if (match) {
                                const currentCount = parseInt(match[1]) - 1;
                                const totalCount = parseInt(match[2]) - 1;
                                totalText.html(`<i class="bi bi-info-circle me-1"></i>Hiển thị ${currentCount} trên tổng số ${totalCount} sản phẩm`);
                                
                                // Nếu không còn sản phẩm trên trang này, chuyển về trang trước
                                if (remainingRows === 0 && currentCount > 0) {
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const currentPage = parseInt(urlParams.get('page') || '1');
                                    const keyword = urlParams.get('keyword') || '';
                                    const categoryId = urlParams.get('category_id') || '';
                                    
                                    // Chuyển về trang trước nếu có
                                    if (currentPage > 1) {
                                        urlParams.set('page', currentPage - 1);
                                        window.location.href = window.location.pathname + '?' + urlParams.toString();
                                        return;
                                    }
                                }
                            }
                        }
                    });
                    
                    // Show success toast
                    showToast(response.message, 'success');
                } else {
                    // Show error toast
                    showToast(response.message, 'danger');
                    // Reset button
                    submitBtn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            },
            error: function(xhr, status, error) {
                showToast('Có lỗi xảy ra khi xóa sản phẩm.', 'danger');
                submitBtn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
            }
        });
    });

    // AJAX update product
    $(document).on('submit', '.update-product-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang cập nhật...');

        // Create FormData for file upload
        const formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success toast
                    showToast(response.message, 'success');
                    
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    // Redirect back to products list after success
                    setTimeout(function() {
                        // Use redirect URL from response, fallback to products list
                        const redirectUrl = response.redirect_url || (window.BASE_URL + '/admin/products');
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                    // Show error toast
                    showToast(response.message, 'danger');
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                showToast('Có lỗi xảy ra khi cập nhật sản phẩm.', 'danger');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Image preview enhancement
    $(document).on('change', 'input[type="file"][accept*="image"]', function() {
        const input = $(this);
        const previewContainer = input.closest('.card-body').find('#image_preview, .image-preview');
        const previewImg = previewContainer.find('img');

        if (input[0].files && input[0].files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                if (previewImg.length) {
                    previewImg.attr('src', e.target.result);
                } else {
                    previewContainer.html(`<img src="${e.target.result}" alt="Preview" class="img-thumbnail mt-2" style="max-width: 200px;">`);
                }
                previewContainer.removeClass('d-none');
            };

            reader.readAsDataURL(input[0].files[0]);
        } else {
            previewContainer.addClass('d-none');
        }
    });

    // Auto-hide alerts after 5 seconds
    $('.alert').each(function() {
        const alert = $(this);
        setTimeout(function() {
            alert.fadeOut(300, function() {
                alert.alert('close');
            });
        }, 5000);
    });

    // Enhanced tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Table row hover effects
    $('.table-hover tbody tr').hover(
        function() {
            $(this).addClass('table-active');
        },
        function() {
            $(this).removeClass('table-active');
        }
    );

    // Search input enhancement
    $('.table-search input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const table = $(this).closest('.card').find('tbody');

        table.find('tr').each(function() {
            const row = $(this);
            const text = row.text().toLowerCase();

            if (text.includes(searchTerm)) {
                row.show();
            } else {
                row.hide();
            }
        });
    });

    // Pagination enhancement
    $('.pagination .page-link').on('click', function(e) {
        e.preventDefault();
        const link = $(this);
        const url = link.attr('href');

        if (url && url !== '#') {
            // Add loading state
            link.html('<span class="spinner-border spinner-border-sm"></span>');

            // In a real SPA, you'd use AJAX here
            // For now, just navigate
            window.location.href = url;
        }
    });

    // Toast notification system
    window.showToast = function(message, type = 'info') {
        const toastContainer = $('#toastContainer');
        const toastId = 'toast-' + Date.now();

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        toastContainer.append(toastHtml);

        const toast = new bootstrap.Toast(document.getElementById(toastId), {
            delay: 4000
        });
        toast.show();

        // Remove from DOM after hiding
        $(`#${toastId}`).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    };

    // Form validation enhancement
    $(document).on('submit', 'form', function(e) {
        const form = $(this);
        let isValid = true;

        // Check required fields
        form.find('[required]').each(function() {
            const field = $(this);
            if (!field.val().trim()) {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields.', 'warning');
            return false;
        }
    });

    // Clear validation on input
    $(document).on('input', '.form-control, .form-select', function() {
        $(this).removeClass('is-invalid');
    });

    // Modal form handling
    $(document).on('show.bs.modal', '.modal', function() {
        // Reset forms in modals
        $(this).find('form').trigger('reset');
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S to submit forms
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('form').first().trigger('submit');
        }

        // Escape to close modals
        if (e.key === 'Escape') {
            $('.modal.show').modal('hide');
        }
    });

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 300);
        }
    });

    // Initialize any existing toasts from flash messages
    if ($('.alert').length) {
        $('.alert').each(function() {
            const alert = $(this);
            const type = alert.hasClass('alert-success') ? 'success' :
                        alert.hasClass('alert-danger') ? 'error' :
                        alert.hasClass('alert-warning') ? 'warning' : 'info';
            const message = alert.text().trim();

            // Convert alert to toast after a delay
            setTimeout(function() {
                showToast(message, type);
                alert.fadeOut();
            }, 1000);
        });
    }
});