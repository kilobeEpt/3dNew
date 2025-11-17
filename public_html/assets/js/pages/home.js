import { api } from '../api.js';
import { Slider } from '../slider.js';
import { formatDate, truncate, setupFadeInAnimations } from '../utils.js';

class HomePage {
    constructor() {
        this.init();
    }

    async init() {
        this.updateCurrentYear();
        await this.loadSettings();
        await this.loadFeaturedServices();
        await this.loadFeaturedGallery();
        this.initTestimonialSlider();
        setupFadeInAnimations();
    }

    updateCurrentYear() {
        const yearEl = document.getElementById('current-year');
        if (yearEl) {
            yearEl.textContent = new Date().getFullYear().toString();
        }
    }

    async loadSettings() {
        try {
            const response = await api.getSettings();
            if (response.success && response.data) {
                this.updateContactInfo(response.data);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    updateContactInfo(settings) {
        const emailEl = document.getElementById('footer-email');
        const phoneEl = document.getElementById('footer-phone');
        const addressEl = document.getElementById('footer-address');

        if (emailEl && settings.contact_email) {
            emailEl.textContent = settings.contact_email;
        }
        if (phoneEl && settings.contact_phone) {
            phoneEl.textContent = settings.contact_phone;
        }
        if (addressEl && settings.contact_address) {
            addressEl.textContent = settings.contact_address;
        }
    }

    async loadFeaturedServices() {
        const container = document.getElementById('services-grid');
        if (!container) return;

        try {
            const response = await api.getServices({ featured: true, per_page: 6 });
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                container.innerHTML = '';
                response.data.data.slice(0, 6).forEach(service => {
                    container.appendChild(this.createServiceCard(service));
                });
            } else {
                container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1;">No services available at this time.</p>';
            }
        } catch (error) {
            console.error('Error loading services:', error);
            container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-error);">Failed to load services. Please try again later.</p>';
        }
    }

    createServiceCard(service) {
        const card = document.createElement('article');
        card.className = 'card service-card fade-in';

        const imageUrl = service.image_url || '/assets/images/placeholder-service.jpg';
        const description = service.description || service.short_description || '';

        card.innerHTML = `
            <div class="service-card-header">
                <img src="${imageUrl}" alt="${service.name}" class="service-card-img" loading="lazy">
                ${service.is_featured ? '<span class="badge badge-primary service-card-badge">Featured</span>' : ''}
            </div>
            <div class="service-card-body">
                <h3 class="card-title">${service.name}</h3>
                <p class="card-text">${truncate(description, 150)}</p>
            </div>
            <div class="service-card-footer">
                <a href="/service.html?id=${service.id}" class="btn btn-primary btn-sm">Learn More</a>
            </div>
        `;

        setupFadeInAnimations();
        return card;
    }

    async loadFeaturedGallery() {
        const container = document.getElementById('gallery-grid');
        if (!container) return;

        try {
            const response = await api.getGallery({ featured: true, per_page: 6 });
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                container.innerHTML = '';
                response.data.data.slice(0, 6).forEach(item => {
                    container.appendChild(this.createGalleryItem(item));
                });
            } else {
                container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1;">No gallery items available at this time.</p>';
            }
        } catch (error) {
            console.error('Error loading gallery:', error);
            container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-error);">Failed to load gallery. Please try again later.</p>';
        }
    }

    createGalleryItem(item) {
        const galleryItem = document.createElement('article');
        galleryItem.className = 'gallery-item fade-in';
        
        const imageUrl = item.image_url || '/assets/images/placeholder-gallery.jpg';

        galleryItem.innerHTML = `
            <img src="${imageUrl}" alt="${item.title}" class="gallery-item-img" loading="lazy">
            <div class="gallery-item-overlay">
                <h3 class="gallery-item-title">${item.title}</h3>
                ${item.description ? `<p class="gallery-item-description">${truncate(item.description, 100)}</p>` : ''}
            </div>
        `;

        galleryItem.addEventListener('click', () => {
            window.location.href = '/gallery.html';
        });

        galleryItem.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = '/gallery.html';
            }
        });

        galleryItem.setAttribute('tabindex', '0');
        galleryItem.setAttribute('role', 'button');
        galleryItem.setAttribute('aria-label', `View ${item.title} in gallery`);

        setupFadeInAnimations();
        return galleryItem;
    }

    initTestimonialSlider() {
        const sliderEl = document.getElementById('testimonials');
        if (sliderEl) {
            new Slider(sliderEl, {
                autoplay: true,
                interval: 6000
            });
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new HomePage();
    });
} else {
    new HomePage();
}

export { HomePage };
