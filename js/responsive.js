/**
 * Responsive JavaScript for Mobile Navigation and Touch Interactions
 */

// Mobile Menu Toggle Function
function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (nav && toggle) {
        nav.classList.toggle('active');
        toggle.classList.toggle('active');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const nav = document.querySelector('.main-nav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (nav && toggle && nav.classList.contains('active')) {
        if (!nav.contains(event.target) && !toggle.contains(event.target)) {
            nav.classList.remove('active');
            toggle.classList.remove('active');
        }
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 768) {
        const nav = document.querySelector('.main-nav');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (nav && toggle) {
            nav.classList.remove('active');
            toggle.classList.remove('active');
        }
    }
});

// Smooth scrolling for anchor links (if any)
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Touch-friendly improvements for mobile devices
if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
    // Add touch class to body for CSS targeting
    document.body.classList.add('touch-device');
    
    // Improve touch interactions for buttons and links
    const touchElements = document.querySelectorAll('button, .btn, a');
    
    touchElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        element.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
}

// Lazy loading for images on mobile (performance improvement)
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    const lazyImages = document.querySelectorAll('img.lazy');
    lazyImages.forEach(img => imageObserver.observe(img));
}

// Responsive table handling for mobile
function makeTablesResponsive() {
    const tables = document.querySelectorAll('.table-responsive table');
    
    tables.forEach(table => {
        if (window.innerWidth <= 767) {
            // Add mobile-friendly table handling if needed
            table.style.fontSize = '12px';
        } else {
            table.style.fontSize = '';
        }
    });
}

// Run on load and resize
window.addEventListener('load', makeTablesResponsive);
window.addEventListener('resize', makeTablesResponsive);

// Prevent zoom on input focus for iOS
function preventZoomOnInputs() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.style.fontSize === '' || input.style.fontSize === 'inherit') {
            input.style.fontSize = '16px'; // Prevents zoom on iOS
        }
    });
}

// Run when DOM is ready
document.addEventListener('DOMContentLoaded', preventZoomOnInputs);

// Cart quantity update improvements for mobile
function enhanceMobileCartExperience() {
    const quantityInputs = document.querySelectorAll('.quantity');
    
    quantityInputs.forEach(input => {
        // Add mobile-friendly increment/decrement buttons
        if (window.innerWidth <= 767 && !input.parentNode.querySelector('.qty-controls')) {
            const controlsDiv = document.createElement('div');
            controlsDiv.className = 'qty-controls';
            controlsDiv.style.cssText = 'display: flex; align-items: center; gap: 5px; margin-top: 5px;';
            
            const decreaseBtn = document.createElement('button');
            decreaseBtn.innerHTML = '-';
            decreaseBtn.type = 'button';
            decreaseBtn.style.cssText = 'width: 30px; height: 30px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px;';
            decreaseBtn.addEventListener('click', function() {
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            const increaseBtn = document.createElement('button');
            increaseBtn.innerHTML = '+';
            increaseBtn.type = 'button';
            increaseBtn.style.cssText = 'width: 30px; height: 30px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px;';
            increaseBtn.addEventListener('click', function() {
                if (parseInt(input.value) < 10) {
                    input.value = parseInt(input.value) + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            controlsDiv.appendChild(decreaseBtn);
            controlsDiv.appendChild(increaseBtn);
            input.parentNode.appendChild(controlsDiv);
        }
    });
}

// Run cart enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', enhanceMobileCartExperience);
window.addEventListener('resize', enhanceMobileCartExperience);
