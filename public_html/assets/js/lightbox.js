class Lightbox {
    constructor(items = []) {
        this.items = items;
        this.currentIndex = 0;
        this.element = null;
        this.init();
    }

    init() {
        this.createElements();
        this.setupEventListeners();
    }

    createElements() {
        this.element = document.createElement('div');
        this.element.className = 'lightbox';
        this.element.setAttribute('role', 'dialog');
        this.element.setAttribute('aria-modal', 'true');
        this.element.setAttribute('aria-label', 'Gallery lightbox');
        this.element.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close" aria-label="Close lightbox">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <button class="lightbox-nav lightbox-prev" aria-label="Previous image">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button class="lightbox-nav lightbox-next" aria-label="Next image">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <img class="lightbox-img" src="" alt="">
                <div class="lightbox-caption">
                    <div class="lightbox-title"></div>
                    <div class="lightbox-description"></div>
                </div>
            </div>
        `;
        document.body.appendChild(this.element);

        this.img = this.element.querySelector('.lightbox-img');
        this.titleEl = this.element.querySelector('.lightbox-title');
        this.descriptionEl = this.element.querySelector('.lightbox-description');
        this.closeBtn = this.element.querySelector('.lightbox-close');
        this.prevBtn = this.element.querySelector('.lightbox-prev');
        this.nextBtn = this.element.querySelector('.lightbox-next');
    }

    setupEventListeners() {
        this.closeBtn.addEventListener('click', () => this.close());
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());

        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) {
                this.close();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!this.element.classList.contains('open')) return;

            switch (e.key) {
                case 'Escape':
                    this.close();
                    break;
                case 'ArrowLeft':
                    this.prev();
                    break;
                case 'ArrowRight':
                    this.next();
                    break;
            }
        });
    }

    open(index = 0) {
        if (!this.items.length) return;

        this.currentIndex = index;
        this.show();
        this.element.classList.add('open');
        document.body.style.overflow = 'hidden';
        
        this.closeBtn.focus();
    }

    close() {
        this.element.classList.remove('open');
        document.body.style.overflow = '';
    }

    show() {
        const item = this.items[this.currentIndex];
        if (!item) return;

        this.img.src = item.image_url || item.src;
        this.img.alt = item.title || item.alt || '';
        this.titleEl.textContent = item.title || '';
        this.descriptionEl.textContent = item.description || '';

        this.prevBtn.disabled = this.currentIndex === 0;
        this.nextBtn.disabled = this.currentIndex === this.items.length - 1;

        const currentNumber = this.currentIndex + 1;
        const totalImages = this.items.length;
        this.element.setAttribute('aria-label', `Gallery lightbox: Image ${currentNumber} of ${totalImages}`);
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.show();
        }
    }

    next() {
        if (this.currentIndex < this.items.length - 1) {
            this.currentIndex++;
            this.show();
        }
    }

    setItems(items) {
        this.items = items;
    }

    destroy() {
        if (this.element && this.element.parentNode) {
            this.element.parentNode.removeChild(this.element);
        }
    }
}

export { Lightbox };
