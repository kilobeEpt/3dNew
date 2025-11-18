function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
}

function truncate(text, length, suffix = '...') {
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + suffix;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-');
}

function parseQueryString(queryString = window.location.search) {
    const params = new URLSearchParams(queryString);
    const result = {};
    for (const [key, value] of params.entries()) {
        result[key] = value;
    }
    return result;
}

function updateQueryString(params) {
    const url = new URL(window.location.href);
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    window.history.pushState({}, '', url.toString());
}

function setupIntersectionObserver(elements, callback, options = {}) {
    const defaultOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1,
        ...options
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                callback(entry.target);
                if (options.once) {
                    observer.unobserve(entry.target);
                }
            }
        });
    }, defaultOptions);

    elements.forEach(element => observer.observe(element));

    return observer;
}

function showLoading(container) {
    const loadingEl = document.createElement('div');
    loadingEl.className = 'loading-overlay';
    loadingEl.innerHTML = '<div class="spinner"></div>';
    loadingEl.setAttribute('role', 'status');
    loadingEl.setAttribute('aria-live', 'polite');
    loadingEl.setAttribute('aria-label', 'Loading content');
    container.appendChild(loadingEl);
    return loadingEl;
}

function hideLoading(loadingEl) {
    if (loadingEl && loadingEl.parentNode) {
        loadingEl.parentNode.removeChild(loadingEl);
    }
}

function showError(container, message) {
    const errorEl = document.createElement('div');
    errorEl.className = 'alert alert-error';
    errorEl.setAttribute('role', 'alert');
    errorEl.textContent = message;
    container.appendChild(errorEl);
    return errorEl;
}

function showSuccess(container, message) {
    const successEl = document.createElement('div');
    successEl.className = 'alert alert-success';
    successEl.setAttribute('role', 'status');
    successEl.textContent = message;
    container.appendChild(successEl);
    return successEl;
}

function setupBackToTop() {
    const button = document.querySelector('.back-to-top');
    if (!button) return;

    const showThreshold = 300;

    const handleScroll = throttle(() => {
        if (window.pageYOffset > showThreshold) {
            button.classList.add('visible');
        } else {
            button.classList.remove('visible');
        }
    }, 100);

    window.addEventListener('scroll', handleScroll);

    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

function setupFadeInAnimations() {
    const elements = document.querySelectorAll('.fade-in');
    setupIntersectionObserver(elements, (element) => {
        element.classList.add('visible');
    }, { once: true, threshold: 0.1 });
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\d\s\-\+\(\)]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

function validateForm(form, rules) {
    const errors = {};
    const data = new FormData(form);

    Object.keys(rules).forEach(fieldName => {
        const value = data.get(fieldName);
        const fieldRules = rules[fieldName];

        if (fieldRules.required && !value) {
            errors[fieldName] = fieldRules.requiredMessage || `${fieldName} is required`;
        } else if (value) {
            if (fieldRules.email && !validateEmail(value)) {
                errors[fieldName] = fieldRules.emailMessage || 'Invalid email address';
            }
            if (fieldRules.phone && !validatePhone(value)) {
                errors[fieldName] = fieldRules.phoneMessage || 'Invalid phone number';
            }
            if (fieldRules.minLength && value.length < fieldRules.minLength) {
                errors[fieldName] = fieldRules.minLengthMessage || `Minimum ${fieldRules.minLength} characters required`;
            }
            if (fieldRules.maxLength && value.length > fieldRules.maxLength) {
                errors[fieldName] = fieldRules.maxLengthMessage || `Maximum ${fieldRules.maxLength} characters allowed`;
            }
        }
    });

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

function displayFormErrors(form, errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('input-error');
            
            const errorEl = document.createElement('span');
            errorEl.className = 'form-error';
            errorEl.textContent = errors[fieldName];
            errorEl.setAttribute('role', 'alert');
            
            const existingError = field.parentNode.querySelector('.form-error');
            if (existingError) {
                existingError.remove();
            }
            
            field.parentNode.appendChild(errorEl);
        }
    });
}

function clearFormErrors(form) {
    const errorFields = form.querySelectorAll('.input-error');
    errorFields.forEach(field => field.classList.remove('input-error'));
    
    const errorMessages = form.querySelectorAll('.form-error');
    errorMessages.forEach(message => message.remove());
}

export {
    debounce,
    throttle,
    formatDate,
    formatCurrency,
    truncate,
    escapeHtml,
    slugify,
    parseQueryString,
    updateQueryString,
    setupIntersectionObserver,
    showLoading,
    hideLoading,
    showError,
    showSuccess,
    setupBackToTop,
    setupFadeInAnimations,
    validateEmail,
    validatePhone,
    validateForm,
    displayFormErrors,
    clearFormErrors
};
