import { api } from '../api.js';
import { parseQueryString, updateQueryString, truncate, setupFadeInAnimations } from '../utils.js';

class ServicesPage {
    constructor() {
        this.currentPage = 1;
        this.perPage = 12;
        this.selectedCategory = null;
        this.init();
    }

    async init() {
        const params = parseQueryString();
        this.currentPage = parseInt(params.page) || 1;
        this.selectedCategory = params.category || null;
        
        await this.loadCategories();
        await this.loadServices();
        setupFadeInAnimations();
    }

    async loadCategories() {
        const container = document.getElementById('services-filters');
        if (!container) return;

        const allBtn = document.createElement('button');
        allBtn.className = `gallery-filter ${!this.selectedCategory ? 'active' : ''}`;
        allBtn.textContent = 'All Services';
        allBtn.setAttribute('aria-pressed', !this.selectedCategory);
        allBtn.addEventListener('click', () => this.filterByCategory(null));
        container.appendChild(allBtn);
    }

    async loadServices() {
        const container = document.getElementById('services-grid');
        if (!container) return;

        try {
            const params = {
                page: this.currentPage,
                per_page: this.perPage
            };
            if (this.selectedCategory) {
                params.category = this.selectedCategory;
            }

            const response = await api.getServices(params);
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                container.innerHTML = '';
                response.data.data.forEach(service => {
                    container.appendChild(this.createServiceCard(service));
                });
                
                if (response.data.pagination) {
                    this.renderPagination(response.data.pagination);
                }
            } else {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <h3 class="empty-state-title">No Services Found</h3>
                        <p class="empty-state-description">Check back later for new services.</p>
                    </div>
                `;
            }
            setupFadeInAnimations();
        } catch (error) {
            console.error('Error loading services:', error);
            container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-error);">Failed to load services.</p>';
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

        return card;
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
        
        this.loadServices();
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
        this.loadServices();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ServicesPage();
    });
} else {
    new ServicesPage();
}

export { ServicesPage };
