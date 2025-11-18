class Navigation {
    constructor() {
        this.header = document.querySelector('.header');
        this.mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        this.mobileMenu = document.querySelector('.mobile-menu');
        this.mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
        this.isMenuOpen = false;
        this.init();
    }

    init() {
        this.setupScrollListener();
        this.setupMobileMenu();
        this.highlightCurrentPage();
        this.setupAccessibility();
    }

    setupScrollListener() {
        if (!this.header) return;

        let lastScroll = 0;
        const scrollThreshold = 50;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > scrollThreshold) {
                this.header.classList.add('scrolled');
            } else {
                this.header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    }

    setupMobileMenu() {
        if (!this.mobileMenuToggle || !this.mobileMenu) return;

        this.mobileMenuToggle.addEventListener('click', () => {
            this.toggleMobileMenu();
        });

        if (this.mobileMenuOverlay) {
            this.mobileMenuOverlay.addEventListener('click', () => {
                this.closeMobileMenu();
            });
        }

        const mobileMenuLinks = this.mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                this.closeMobileMenu();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }

    toggleMobileMenu() {
        if (this.isMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }

    openMobileMenu() {
        this.isMenuOpen = true;
        this.mobileMenu.classList.add('open');
        if (this.mobileMenuOverlay) {
            this.mobileMenuOverlay.classList.add('open');
        }
        this.mobileMenuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';

        const firstLink = this.mobileMenu.querySelector('a');
        if (firstLink) {
            firstLink.focus();
        }
    }

    closeMobileMenu() {
        this.isMenuOpen = false;
        this.mobileMenu.classList.remove('open');
        if (this.mobileMenuOverlay) {
            this.mobileMenuOverlay.classList.remove('open');
        }
        this.mobileMenuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    highlightCurrentPage() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.header-nav-link, .mobile-menu-link');

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPath || (currentPath !== '/' && linkPath !== '/' && currentPath.startsWith(linkPath))) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            } else {
                link.classList.remove('active');
                link.removeAttribute('aria-current');
            }
        });
    }

    setupAccessibility() {
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.setAttribute('aria-expanded', 'false');
            this.mobileMenuToggle.setAttribute('aria-label', 'Toggle mobile menu');
        }

        if (this.mobileMenu) {
            this.mobileMenu.setAttribute('role', 'navigation');
            this.mobileMenu.setAttribute('aria-label', 'Mobile navigation');
        }
    }
}

export { Navigation };
