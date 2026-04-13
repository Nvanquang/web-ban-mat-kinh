/**
 * Eyeglass Online - Main JavaScript File
 * Handles UI interactions, animations, and enhance user experience.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Sticky Navbar Effect on Scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-sm');
                navbar.style.paddingTop = '10px';
                navbar.style.paddingBottom = '10px';
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.classList.remove('shadow-sm');
                navbar.style.paddingTop = '15px';
                navbar.style.paddingBottom = '15px';
                navbar.style.backgroundColor = '#fff';
                navbar.style.backdropFilter = 'none';
            }
        });
    }

    // 2. Intersection Observer for Scroll Animations
    // Animate elements as they enter the viewport
    const animatedElements = document.querySelectorAll('.feature-box, .product-card, .hero-content');
    
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.visibility = 'visible';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Initial setup for animated elements
    animatedElements.forEach(el => {
        // Skip elements that already have animation from CSS
        if (window.getComputedStyle(el).animationName === 'none') {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        }
    });

    // 3. Add to Cart Button Interactions (Ripple / Loading effect)
    const cartButtons = document.querySelectorAll('.btn-outline-primary, .btn-primary');
    cartButtons.forEach(button => {
        if(button.innerHTML.includes('Giỏ Hàng') || button.innerHTML.includes('Mua Ngay')) {
            button.addEventListener('click', function(e) {
                // If it's a link, we don't prevent default, but we can add an active class temporarily
                const originalText = this.innerHTML;
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    // Add a tiny generic spinner icon (Bootstrap icon)
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang xử lý...';
                    
                    // Note: If this button is a standard <a> link to a new page, 
                    // the page will unload before the timeout finishes. This is just for UX feel.
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Thành công';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-success');
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-success');
                            this.classList.remove('loading');
                            this.classList.add('btn-outline-primary');
                        }, 2000);
                    }, 800);
                }
            });
        }
    });

    // 4. Newsletter Form Highlight
    const newsletterForm = document.querySelector('form input[type="email"]');
    if (newsletterForm) {
        const formParent = newsletterForm.closest('form');
        formParent.addEventListener('submit', (e) => {
            e.preventDefault(); // For demo purposes
            const btn = formParent.querySelector('button');
            const originalBtnText = btn.innerText;
            
            if (newsletterForm.value) {
                btn.innerHTML = 'Đã Đăng Ký! 🎉';
                btn.classList.add('bg-success', 'text-white', 'border-success');
                btn.classList.remove('btn-warning', 'text-dark');
                newsletterForm.value = '';
                
                setTimeout(() => {
                    btn.innerHTML = originalBtnText;
                    btn.classList.remove('bg-success', 'text-white', 'border-success');
                    btn.classList.add('btn-warning', 'text-dark');
                }, 3000);
            }
        });
    }

    // 5. Setup tooltip/popovers if Bootstrap is present
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
