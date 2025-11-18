import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { Modal } from '../components/modal.js';
import { State } from '../state.js';
import { formatDate, getStatusBadgeClass, debounce } from '../utils/helpers.js';

export class ServicesView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.currentPage = 1;
        this.perPage = 20;
        this.searchTerm = '';
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            await this.loadServices();
        } catch (error) {
            console.error('Services error:', error);
            Toast.error('Error', 'Failed to load services');
        }
    }

    async loadServices() {
        const params = {
            page: this.currentPage,
            per_page: this.perPage
        };

        if (this.searchTerm) {
            params.search = this.searchTerm;
        }

        const response = await API.get('/admin/services', params);
        this.renderContent(response.data);
    }

    renderContent(data) {
        const canWrite = State.canWrite();
        const canDelete = State.canDelete();

        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-left">
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Search services..." 
                        value="${this.searchTerm}"
                        style="padding: var(--spacing-2) var(--spacing-3); border: 1px solid var(--color-border); border-radius: var(--border-radius-lg); width: 300px;"
                    >
                </div>
                <div class="toolbar-right">
                    ${canWrite ? '<button class="btn btn-primary" id="add-service-btn"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg><span>Add Service</span></button>' : ''}
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.map(service => this.renderServiceRow(service, canWrite, canDelete)).join('')}
                        </tbody>
                    </table>
                </div>
            </div>

            ${this.renderPagination(data.pagination)}
        `;

        this.attachEventListeners();
    }

    renderServiceRow(service, canWrite, canDelete) {
        return `
            <tr>
                <td><strong>${service.name}</strong></td>
                <td>${service.category_name || '-'}</td>
                <td><span class="badge badge-primary">${service.price_type}</span></td>
                <td><span class="badge ${service.is_visible ? 'badge-success' : 'badge-secondary'}">${service.is_visible ? 'Visible' : 'Hidden'}</span></td>
                <td>${formatDate(service.created_at)}</td>
                <td>
                    <div class="table-actions">
                        ${canWrite ? `<button class="btn btn-sm btn-secondary" onclick="window.editService(${service.id})">Edit</button>` : ''}
                        ${canDelete ? `<button class="btn btn-sm btn-danger" onclick="window.deleteService(${service.id})">Delete</button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }

    renderPagination(pagination) {
        if (!pagination || pagination.last_page <= 1) return '';

        const pages = [];
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                pages.push(i);
            } else if (pages[pages.length - 1] !== '...') {
                pages.push('...');
            }
        }

        return `
            <div class="pagination">
                <button class="pagination-btn" ${pagination.current_page === 1 ? 'disabled' : ''} onclick="window.goToPage(${pagination.current_page - 1})">Previous</button>
                ${pages.map(page => 
                    page === '...' 
                        ? '<span>...</span>'
                        : `<button class="pagination-btn ${page === pagination.current_page ? 'active' : ''}" onclick="window.goToPage(${page})">${page}</button>`
                ).join('')}
                <button class="pagination-btn" ${pagination.current_page === pagination.last_page ? 'disabled' : ''} onclick="window.goToPage(${pagination.current_page + 1})">Next</button>
            </div>
        `;
    }

    attachEventListeners() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.loadServices();
            }, 500));
        }

        const addBtn = document.getElementById('add-service-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showServiceForm());
        }

        window.goToPage = (page) => {
            this.currentPage = page;
            this.loadServices();
        };

        window.editService = (id) => this.showServiceForm(id);
        window.deleteService = (id) => this.deleteService(id);
    }

    async showServiceForm(id = null) {
        const isEdit = !!id;
        let service = null;

        if (isEdit) {
            const response = await API.get(`/admin/services/${id}`);
            service = response.data;
        }

        const content = `
            <form id="service-form">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="${service?.name || ''}" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description">${service?.description || ''}</textarea>
                </div>
                <div class="form-group">
                    <label for="price_type">Price Type *</label>
                    <select id="price_type" name="price_type" required>
                        <option value="fixed" ${service?.price_type === 'fixed' ? 'selected' : ''}>Fixed</option>
                        <option value="quote" ${service?.price_type === 'quote' ? 'selected' : ''}>Quote</option>
                        <option value="calculator" ${service?.price_type === 'calculator' ? 'selected' : ''}>Calculator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_visible" name="is_visible" ${service?.is_visible !== false ? 'checked' : ''}>
                        Visible
                    </label>
                </div>
            </form>
        `;

        const modal = new Modal({
            title: isEdit ? 'Edit Service' : 'Add Service',
            content,
            confirmText: 'Save',
            onConfirm: async () => {
                await this.saveService(id);
            }
        });

        await modal.show();
    }

    async saveService(id) {
        const form = document.getElementById('service-form');
        const formData = new FormData(form);
        
        const data = {
            name: formData.get('name'),
            description: formData.get('description'),
            price_type: formData.get('price_type'),
            is_visible: formData.get('is_visible') ? true : false
        };

        try {
            if (id) {
                await API.put(`/admin/services/${id}`, data);
                Toast.success('Success', 'Service updated successfully');
            } else {
                await API.post('/admin/services', data);
                Toast.success('Success', 'Service created successfully');
            }
            
            await this.loadServices();
        } catch (error) {
            Toast.error('Error', error.message || 'Failed to save service');
            throw error;
        }
    }

    async deleteService(id) {
        try {
            await Modal.confirm('Delete Service', 'Are you sure you want to delete this service?');
            
            await API.delete(`/admin/services/${id}`);
            Toast.success('Success', 'Service deleted successfully');
            await this.loadServices();
        } catch (error) {
            if (error !== false) {
                Toast.error('Error', 'Failed to delete service');
            }
        }
    }
}
