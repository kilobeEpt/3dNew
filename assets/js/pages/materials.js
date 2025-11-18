import { api } from '../api.js';
import { parseQueryString, updateQueryString, formatCurrency, truncate, setupFadeInAnimations } from '../utils.js';

class MaterialsPage {
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
        await this.loadMaterials();
        setupFadeInAnimations();
    }

    async loadCategories() {
        const container = document.getElementById('material-categories');
        if (!container) return;

        try {
            const response = await api.getMaterialCategories();
            
            const allBtn = document.createElement('button');
            allBtn.className = `material-category ${!this.selectedCategory ? 'active' : ''}`;
            allBtn.textContent = 'All Materials';
            allBtn.setAttribute('aria-pressed', !this.selectedCategory ? 'true' : 'false');
            allBtn.addEventListener('click', () => this.filterByCategory(null));
            container.appendChild(allBtn);

            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(category => {
                    const btn = document.createElement('button');
                    btn.className = `material-category ${this.selectedCategory === category ? 'active' : ''}`;
                    btn.textContent = category;
                    btn.setAttribute('aria-pressed', this.selectedCategory === category ? 'true' : 'false');
                    btn.addEventListener('click', () => this.filterByCategory(category));
                    container.appendChild(btn);
                });
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadMaterials() {
        const container = document.getElementById('materials-grid');
        if (!container) return;

        try {
            const params = {
                page: this.currentPage,
                per_page: this.perPage
            };
            if (this.selectedCategory) {
                params.category = this.selectedCategory;
            }

            const response = await api.getMaterials(params);
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                container.innerHTML = '';
                response.data.data.forEach(material => {
                    container.appendChild(this.createMaterialCard(material));
                });
                
                if (response.data.pagination) {
                    this.renderPagination(response.data.pagination);
                }
            } else {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <h3 class="empty-state-title">No Materials Found</h3>
                        <p class="empty-state-description">Check back later for new materials.</p>
                    </div>
                `;
            }
            setupFadeInAnimations();
        } catch (error) {
            console.error('Error loading materials:', error);
            container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-error);">Failed to load materials.</p>';
        }
    }

    createMaterialCard(material) {
        const card = document.createElement('article');
        card.className = 'card material-card fade-in';

        const price = material.unit_price ? formatCurrency(material.unit_price) : 'Contact for price';
        const unit = material.unit || 'unit';
        const description = material.description || material.specifications || '';

        card.innerHTML = `
            <div class="material-card-header">
                <div>
                    <h3 class="card-title">${material.name}</h3>
                    ${material.category ? `<span class="badge">${material.category}</span>` : ''}
                </div>
                <div>
                    <div class="material-card-price">${price}</div>
                    <div class="material-card-unit">per ${unit}</div>
                </div>
            </div>
            <div class="material-card-body">
                <p class="card-text">${truncate(description, 150)}</p>
                ${material.specifications ? `<p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-tertiary);">${truncate(material.specifications, 100)}</p>` : ''}
            </div>
        `;

        return card;
    }

    filterByCategory(category) {
        this.selectedCategory = category;
        this.currentPage = 1;
        updateQueryString({ category: category, page: 1 });
        
        const categories = document.querySelectorAll('.material-category');
        categories.forEach(cat => {
            cat.classList.remove('active');
            cat.setAttribute('aria-pressed', 'false');
        });
        event.target.classList.add('active');
        event.target.setAttribute('aria-pressed', 'true');
        
        this.loadMaterials();
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
        this.loadMaterials();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new MaterialsPage();
    });
} else {
    new MaterialsPage();
}

export { MaterialsPage };
