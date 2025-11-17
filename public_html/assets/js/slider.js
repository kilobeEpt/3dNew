class Slider {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            autoplay: options.autoplay !== false,
            interval: options.interval || 5000,
            ...options
        };
        this.currentIndex = 0;
        this.slides = [];
        this.isPlaying = false;
        this.intervalId = null;
        this.init();
    }

    init() {
        this.track = this.element.querySelector('.testimonial-track');
        this.slides = Array.from(this.element.querySelectorAll('.testimonial-slide'));
        
        if (this.slides.length === 0) return;

        this.createControls();
        this.createIndicators();
        this.setupEventListeners();
        this.setupAccessibility();

        if (this.options.autoplay) {
            this.play();
        }

        this.goToSlide(0);
    }

    createControls() {
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'testimonial-controls';
        controlsContainer.innerHTML = `
            <button class="testimonial-control testimonial-prev" aria-label="Previous testimonial">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="testimonial-control testimonial-next" aria-label="Next testimonial">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `;
        this.element.appendChild(controlsContainer);

        this.prevBtn = this.element.querySelector('.testimonial-prev');
        this.nextBtn = this.element.querySelector('.testimonial-next');
    }

    createIndicators() {
        const indicatorsContainer = document.createElement('div');
        indicatorsContainer.className = 'testimonial-indicators';
        indicatorsContainer.setAttribute('role', 'tablist');
        
        this.slides.forEach((_, index) => {
            const indicator = document.createElement('button');
            indicator.className = 'testimonial-indicator';
            indicator.setAttribute('role', 'tab');
            indicator.setAttribute('aria-label', `Go to testimonial ${index + 1}`);
            indicator.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
            indicator.dataset.index = index;
            indicatorsContainer.appendChild(indicator);
        });

        this.element.appendChild(indicatorsContainer);
        this.indicators = Array.from(this.element.querySelectorAll('.testimonial-indicator'));
    }

    setupEventListeners() {
        this.prevBtn.addEventListener('click', () => {
            this.prev();
            this.pause();
        });

        this.nextBtn.addEventListener('click', () => {
            this.next();
            this.pause();
        });

        this.indicators.forEach(indicator => {
            indicator.addEventListener('click', () => {
                const index = parseInt(indicator.dataset.index);
                this.goToSlide(index);
                this.pause();
            });
        });

        this.element.addEventListener('mouseenter', () => {
            if (this.options.autoplay) {
                this.pause();
            }
        });

        this.element.addEventListener('mouseleave', () => {
            if (this.options.autoplay && !this.isPlaying) {
                this.play();
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else if (this.options.autoplay && !this.isPlaying) {
                this.play();
            }
        });
    }

    setupAccessibility() {
        this.element.setAttribute('role', 'region');
        this.element.setAttribute('aria-label', 'Testimonials slider');
        this.element.setAttribute('aria-live', 'polite');

        this.slides.forEach((slide, index) => {
            slide.setAttribute('role', 'tabpanel');
            slide.setAttribute('aria-label', `Testimonial ${index + 1} of ${this.slides.length}`);
        });
    }

    goToSlide(index) {
        if (index < 0 || index >= this.slides.length) return;

        this.currentIndex = index;
        const offset = -100 * index;
        this.track.style.transform = `translateX(${offset}%)`;

        this.indicators.forEach((indicator, i) => {
            if (i === index) {
                indicator.classList.add('active');
                indicator.setAttribute('aria-selected', 'true');
            } else {
                indicator.classList.remove('active');
                indicator.setAttribute('aria-selected', 'false');
            }
        });

        this.updateControls();
    }

    updateControls() {
        this.prevBtn.disabled = this.currentIndex === 0;
        this.nextBtn.disabled = this.currentIndex === this.slides.length - 1;
    }

    next() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.goToSlide(nextIndex);
    }

    prev() {
        const prevIndex = this.currentIndex === 0 ? this.slides.length - 1 : this.currentIndex - 1;
        this.goToSlide(prevIndex);
    }

    play() {
        if (this.isPlaying) return;

        this.isPlaying = true;
        this.intervalId = setInterval(() => {
            this.next();
        }, this.options.interval);
    }

    pause() {
        if (!this.isPlaying) return;

        this.isPlaying = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    destroy() {
        this.pause();
        if (this.prevBtn) this.prevBtn.remove();
        if (this.nextBtn) this.nextBtn.remove();
        if (this.indicators) {
            this.indicators.forEach(indicator => indicator.remove());
        }
    }
}

export { Slider };
