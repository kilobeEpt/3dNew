import { api } from '../api.js';
import { Lightbox } from '../lightbox.js';
import { parseQueryString, updateQueryString, setupFadeInAnimations } from '../utils.js';

class GalleryPage {
    constructor() {
        this.currentPage = 1;
        this.perPage = 12;
        this.selectedCategory = null;
        this.items = [];
        this.lightbox = null;
        this.init();
    }

    async init() {
        const params = parseQueryString();
        this.currentPage = parseInt(params.page) || 1;
        this.selectedCategory = params.category || null;
        
        this.lightbox = new Lightbox([]);
        await this.loadFilters();
        await this.loadGallery();
        setupFadeInAnimations();
    }

    async loadFilters() {
        const container = document.getElementById('gallery-filters');
        if (!container) return;

        const allBtn = document.createElement('button');
        allBtn.className = `gallery-filter ${!this.selectedCategory ? 'active' : ''}`;
        allBtn.textContent = 'All';
        allBtn.setAttribute('aria-pressed', !this.selectedCategory ? 'true' : 'false');
        allBtn.addEventListener('click', () => this.filterByCategory(null));
        container.appendChild(allBtn);

        const categories = ['Featured', 'Recent', 'Manufacturing', 'Design'];
        categories.forEach(category => {
            const btn = document.createElement('button');
            btn.className = `gallery-filter ${this.selectedCategory === category.toLowerCase() ? 'active' : ''}`;
            btn.textContent = category;
            btn.setAttribute('aria-pressed', this.selectedCategory === category.toLowerCase() ? 'true' : 'false');
            btn.addEventListener('click', () => this.filterByCategory(category.toLowerCase()));
            container.appendChild(btn);
        });
    }

    async loadGallery() {
        const container = document.getElementById('gallery-grid');
        if (!container) return;

        try {
            const params = {
                page: this.currentPage,
                per_page: this.perPage
            };
            if (this.selectedCategory) {
                params.category = this.selectedCategory;
            }

            const response = await api.getGallery(params);
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                this.items = response.data.data;
                this.lightbox.setItems(this.items);
                
                container.innerHTML = '';
                this.items.forEach((item, index) => {
                    container.appendChild(this.createGalleryItem(item, index));
                });
                
                if (response.data.pagination) {
                    this.renderPagination(response.data.pagination);
                }
            } else {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <h3 class="empty-state-title">No Gallery Items Found</h3>
                        <p class="empty-state-description">Check back later for new projects.</p>
                    </div>
                `;
            }
            setupFadeInAnimations();
        } catch (error) {
            console.error('Error loading gallery:', error);
            container.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: var(--color-error);">Failed to load gallery.</p>';
        }
    }

    createGalleryItem(item, index) {
        const galleryItem = document.createElement('article');
        galleryItem.className = 'gallery-item fade-in';
        
        const imageUrl = item.image_url || '/assets/images/placeholder-gallery.jpg';

        galleryItem.innerHTML = `
            <img src="${imageUrl}" alt="${item.title}" class="gallery-item-img" loading="lazy">
            <div class="gallery-item-overlay">
                <h3 class="gallery-item-title">${item.title}</h3>
                ${item.description ? `<p class="gallery-item-description">${item.description}</p>` : ''}
            </div>
        `;

        galleryItem.addEventListener('click', () => {
            this.lightbox.open(index);
        });

        galleryItem.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.lightbox.open(index);
            }
        });

        galleryItem.setAttribute('tabindex', '0');
        galleryItem.setAttribute('role', 'button');
        galleryItem.setAttribute('aria-label', `View ${item.title} in lightbox`);

        return galleryItem;
    }

    filterByCategory(category) {
        this.selectedCategory = category;
        this.currentPage = 1;
        updateQueryString({ category: category, page: 1 });
        
        const filters = document.querySelectorAll('.gallery-filter');
        filters.forEach(filter => {
            filter.classList.remove('active');
            filter.setAttribute('aria-pressed', 'false');
        });
        event.target.classList.add('active');
        event.target.setAttribute('aria-pressed', 'true');
        
        this.loadGallery();
    }

    renderPagination(pagination) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        if (pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        const paginationEl = document.createElement('div');
        paginationEl.className = 'pagination';

        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '&laquo;';
        prevBtn.disabled = pagination.current_page === 1;
        prevBtn.setAttribute('aria-label', 'Previous page');
        prevBtn.addEventListener('click', () => this.goToPage(pagination.current_page - 1));
        paginationEl.appendChild(prevBtn);

        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-btn ${i === pagination.current_page ? 'active' : ''}`;
            pageBtn.textContent = i.toString();
            pageBtn.setAttribute('aria-label', `Page ${i}`);
            pageBtn.setAttribute('aria-current', i === pagination.current_page ? 'page' : 'false');
            pageBtn.addEventListener('click', () => this.goToPage(i));
            paginationEl.appendChild(pageBtn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = '&raquo;';
        nextBtn.disabled = pagination.current_page === pagination.last_page;
        nextBtn.setAttribute('aria-label', 'Next page');
        nextBtn.addEventListener('click', () => this.goToPage(pagination.current_page + 1));
        paginationEl.appendChild(nextBtn);

        container.innerHTML = '';
        container.appendChild(paginationEl);
    }

    goToPage(page) {
        this.currentPage = page;
        updateQueryString({ page: page });
        this.loadGallery();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new GalleryPage();
    });
} else {
    new GalleryPage();
}

export { GalleryPage };
