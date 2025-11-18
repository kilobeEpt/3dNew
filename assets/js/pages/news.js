import { api } from '../api.js';
import { parseQueryString, updateQueryString, formatDate, truncate, setupFadeInAnimations } from '../utils.js';

class NewsPage {
    constructor() {
        this.currentPage = 1;
        this.perPage = 12;
        this.init();
    }

    async init() {
        const params = parseQueryString();
        this.currentPage = parseInt(params.page) || 1;
        
        await this.loadNews();
        setupFadeInAnimations();
    }

    async loadNews() {
        const container = document.getElementById('news-grid');
        if (!container) return;

        try {
            const params = {
                page: this.currentPage,
                per_page: this.perPage
            };

            const response = await api.getNews(params);
            
            if (response.success && response.data.data && response.data.data.length > 0) {
                container.innerHTML = '';
                response.data.data.forEach(post => {
                    container.appendChild(this.createNewsCard(post));
                });
                
                if (response.data.pagination) {
                    this.renderPagination(response.data.pagination);
                }
            } else {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <h3 class="empty-state-title">No News Posts Found</h3>
                        <p class="empty-state-description">Check back later for updates.</p>
                    </div>
                `;
            }
            setupFadeInAnimations();
        } catch (error) {
            console.error('Error loading news:', error);
            container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-error);">Failed to load news.</p>';
        }
    }

    createNewsCard(post) {
        const card = document.createElement('article');
        card.className = 'card news-card fade-in';

        const imageUrl = post.featured_image || '/assets/images/placeholder-news.jpg';
        const date = post.published_at ? formatDate(post.published_at) : '';
        const excerpt = post.excerpt || post.content || '';

        card.innerHTML = `
            <div class="news-card-header">
                <img src="${imageUrl}" alt="${post.title}" class="news-card-img" loading="lazy">
            </div>
            <div class="news-card-body">
                <div class="news-card-meta">
                    ${date ? `<span>${date}</span>` : ''}
                    ${post.category ? `<span>•</span><span>${post.category}</span>` : ''}
                </div>
                <h3 class="card-title">${post.title}</h3>
                <p class="news-card-excerpt">${truncate(excerpt, 150)}</p>
                <a href="/news-detail.html?id=${post.id}" class="btn btn-primary btn-sm">Read More</a>
            </div>
        `;

        return card;
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
        this.loadNews();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new NewsPage();
    });
} else {
    new NewsPage();
}

export { NewsPage };
