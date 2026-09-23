// main.js - Sarvam Real Estate Complete Frontend JavaScript & Validation
// Phase 2: UI Forms, Interactive Behaviors & Client-Side Validation

document.addEventListener('DOMContentLoaded', () => {
    // 1. Loading Screen - hide after page load
    const loader = document.getElementById('loading-screen');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => loader.remove(), 500);
        }, 500);
    }

    // 2. Navbar scroll effect
    const navbar = document.querySelector('.sarvam-navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }
    });

    // 3. Back-to-Top button
    const backToTopBtn = document.getElementById('btn-back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            backToTopBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 4. Search Tab Switcher (Buy/Rent)
    const searchTabs = document.querySelectorAll('.search-tab, .search-tab-btn');
    searchTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            searchTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Sync with hidden input if present in search form
            const listingTypeInput = document.querySelector('input[name="listing_type"]');
            if (listingTypeInput) {
                listingTypeInput.value = tab.dataset.type || (tab.innerText.toLowerCase().includes('rent') ? 'rent' : 'buy');
            }
        });
    });

    // 5. Property Gallery Lightbox
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeLightbox = document.querySelector('.lightbox-close');

    if (galleryItems.length > 0 && lightbox) {
        galleryItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                if (lightboxImg) {
                    lightboxImg.src = item.dataset.imgSrc || item.querySelector('img')?.src || '';
                }
                lightbox.style.display = 'flex';
            });
        });
        closeLightbox?.addEventListener('click', () => {
            lightbox.style.display = 'none';
        });
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) lightbox.style.display = 'none';
        });
    }

    // 6. Animated Counter Animation (Home Page Stats)
    const counters = document.querySelectorAll('.counter-value, .stat-number');
    const animateCounters = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const rawText = counter.innerText.replace(/[^0-9]/g, '');
                const target = +counter.dataset.target || +rawText || 0;
                if (target === 0) return;
                
                const duration = 1800;
                const increment = Math.max(1, Math.ceil(target / (duration / 20)));
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.innerText = current.toLocaleString() + '+';
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = target.toLocaleString() + '+';
                    }
                };
                updateCounter();
                observer.unobserve(counter);
            }
        });
    };
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver(animateCounters, { threshold: 0.3 });
        counters.forEach(counter => counterObserver.observe(counter));
    }

    // 7. Fade-in on Scroll Animation
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if (animatedElements.length > 0) {
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        animatedElements.forEach(el => fadeObserver.observe(el));
    }

    // 8. Wishlist Interactive Feedback (AJAX)
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const propId = this.dataset.propertyId || this.getAttribute('data-id');
            if (!propId) return;

            try {
                const formData = new FormData();
                formData.append('property_id', propId);
                const res = await fetch('wishlist-action.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data && data.success) {
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if (icon) {
                        if (data.action === 'added') {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill');
                        } else {
                            icon.classList.remove('bi-heart-fill');
                            icon.classList.add('bi-heart');
                        }
                    }
                }
            } catch (err) {
                console.log('Wishlist toggle handled.');
            }
        });
    });

    // 9. Client-Side Form Validations (Phase 2 Requirement)
    
    // A. Password & Confirm Password Match Validation
    const passwordInputs = document.querySelectorAll('input[name="password"]');
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    
    confirmPasswordInputs.forEach(confirmInput => {
        const form = confirmInput.closest('form');
        const passInput = form ? form.querySelector('input[name="password"], input[name="new_password"]') : null;
        
        if (passInput) {
            // Create helper message container
            let msgBox = confirmInput.parentNode.querySelector('.pwd-match-feedback');
            if (!msgBox) {
                msgBox = document.createElement('div');
                msgBox.className = 'pwd-match-feedback small mt-1';
                confirmInput.parentNode.appendChild(msgBox);
            }

            const checkMatch = () => {
                const passVal = passInput.value;
                const confirmVal = confirmInput.value;
                
                if (!confirmVal) {
                    msgBox.innerText = '';
                    confirmInput.classList.remove('is-valid', 'is-invalid');
                    return true;
                }
                
                if (passVal === confirmVal) {
                    msgBox.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>';
                    confirmInput.classList.remove('is-invalid');
                    confirmInput.classList.add('is-valid');
                    return true;
                } else {
                    msgBox.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</span>';
                    confirmInput.classList.remove('is-valid');
                    confirmInput.classList.add('is-invalid');
                    return false;
                }
            };

            confirmInput.addEventListener('input', checkMatch);
            passInput.addEventListener('input', () => {
                if (confirmInput.value) checkMatch();
            });
        }
    });

    // B. Real-time Email Format Validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val && !emailRegex.test(val)) {
                this.classList.add('is-invalid');
                let err = this.parentNode.querySelector('.email-format-err');
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'email-format-err text-danger small mt-1';
                    err.innerText = 'Please enter a valid email address (e.g. user@example.com)';
                    this.parentNode.appendChild(err);
                }
            } else {
                this.classList.remove('is-invalid');
                const err = this.parentNode.querySelector('.email-format-err');
                if (err) err.remove();
            }
        });

        input.addEventListener('input', function() {
            if (emailRegex.test(this.value.trim())) {
                this.classList.remove('is-invalid');
                const err = this.parentNode.querySelector('.email-format-err');
                if (err) err.remove();
            }
        });
    });

    // C. Phone Number Validation (digits, optional plus, length)
    const phoneInputs = document.querySelectorAll('input[name="phone"], input[name="inq_phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const val = this.value.trim().replace(/[\s-]/g, '');
            const phoneRegex = /^(\+?\d{1,4})?\d{10}$/;
            if (val && !phoneRegex.test(val)) {
                this.classList.add('is-invalid');
                let err = this.parentNode.querySelector('.phone-err');
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'phone-err text-danger small mt-1';
                    err.innerText = 'Please enter a valid 10-digit phone number';
                    this.parentNode.appendChild(err);
                }
            } else {
                this.classList.remove('is-invalid');
                const err = this.parentNode.querySelector('.phone-err');
                if (err) err.remove();
            }
        });
    });

    // D. Number Range Validation (Min Price <= Max Price)
    const minPriceInput = document.querySelector('input[name="min_price"], select[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"], select[name="max_price"]');
    if (minPriceInput && maxPriceInput) {
        const validatePriceRange = () => {
            const min = parseFloat(minPriceInput.value) || 0;
            const max = parseFloat(maxPriceInput.value) || 0;
            if (min > 0 && max > 0 && min > max) {
                maxPriceInput.classList.add('is-invalid');
            } else {
                maxPriceInput.classList.remove('is-invalid');
            }
        };
        minPriceInput.addEventListener('change', validatePriceRange);
        maxPriceInput.addEventListener('change', validatePriceRange);
    }

    // E. General Form Submit Interceptor (Bootstrap-style validation check)
    const allForms = document.querySelectorAll('form.form-sarvam, form.needs-validation');
    allForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check confirm password
            const passInput = form.querySelector('input[name="password"], input[name="new_password"]');
            const confirmInput = form.querySelector('input[name="confirm_password"]');
            if (passInput && confirmInput && passInput.value !== confirmInput.value) {
                e.preventDefault();
                e.stopPropagation();
                confirmInput.focus();
                confirmInput.classList.add('is-invalid');
                return false;
            }

            // HTML5 constraint validation
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // 10. Character Counter for Textarea Fields
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const counterId = (textarea.id || textarea.name || 'textarea') + '-counter';
        let counterDisplay = document.getElementById(counterId);
        
        if (!counterDisplay) {
            counterDisplay = document.createElement('div');
            counterDisplay.id = counterId;
            counterDisplay.className = 'text-muted small mt-1 text-end';
            textarea.parentNode.insertBefore(counterDisplay, textarea.nextSibling);
        }

        const updateCounter = () => {
            const length = textarea.value.length;
            const max = textarea.getAttribute('maxlength');
            counterDisplay.innerText = `${length} / ${max} characters`;
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    // 11. Copy Property Link Button
    const copyLinkBtn = document.getElementById('copy-link-btn');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            navigator.clipboard.writeText(window.location.href).then(() => {
                const originalHTML = copyLinkBtn.innerHTML;
                copyLinkBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
                setTimeout(() => copyLinkBtn.innerHTML = originalHTML, 2000);
            });
        });
    }

    // 12. Password Visibility Toggle Functionality
    const togglePwdBtns = document.querySelectorAll('.toggle-password-btn, [data-toggle="password"]');
    togglePwdBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = targetId ? document.getElementById(targetId) : this.closest('.input-group')?.querySelector('input');
            const icon = this.querySelector('i');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            }
        });
    });
});
