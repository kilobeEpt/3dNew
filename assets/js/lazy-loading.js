/**
 * Lazy Loading Images
 * Implements efficient lazy loading with Intersection Observer API
 */

class LazyLoader {
    constructor(options = {}) {
        this.options = {
            rootMargin: options.rootMargin || '50px',
            threshold: options.threshold || 0.01,
            loadingClass: options.loadingClass || 'lazy-loading',
            loadedClass: options.loadedClass || 'lazy-loaded',
            errorClass: options.errorClass || 'lazy-error',
        };
        
        this.observer = null;
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                this.onIntersection.bind(this),
                {
                    rootMargin: this.options.rootMargin,
                    threshold: this.options.threshold,
                }
            );
            
            this.observeImages();
        } else {
            this.loadAllImages();
        }
    }
    
    observeImages() {
        const images = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        images.forEach(img => {
            if (img.dataset.src) {
                this.observer.observe(img);
            }
        });
    }
    
    onIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    loadImage(img) {
        const src = img.dataset.src;
        const srcset = img.dataset.srcset;
        
        if (!src && !srcset) return;
        
        img.classList.add(this.options.loadingClass);
        
        const tempImg = new Image();
        
        tempImg.onload = () => {
            if (src) img.src = src;
            if (srcset) img.srcset = srcset;
            
            img.classList.remove(this.options.loadingClass);
            img.classList.add(this.options.loadedClass);
            
            delete img.dataset.src;
            delete img.dataset.srcset;
        };
        
        tempImg.onerror = () => {
            img.classList.remove(this.options.loadingClass);
            img.classList.add(this.options.errorClass);
        };
        
        if (srcset) {
            tempImg.srcset = srcset;
        }
        if (src) {
            tempImg.src = src;
        }
    }
    
    loadAllImages() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => this.loadImage(img));
    }
    
    refresh() {
        if (this.observer) {
            this.observeImages();
        }
    }
}

// Initialize lazy loading when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.lazyLoader = new LazyLoader();
    });
} else {
    window.lazyLoader = new LazyLoader();
}

export default LazyLoader;
