import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { Modal } from '../components/modal.js';
import { State } from '../state.js';
import { formatCurrency, formatDate, debounce } from '../utils/helpers.js';

export class MaterialsView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.currentPage = 1;
        this.perPage = 20;
        this.searchTerm = '';
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const params = { page: this.currentPage, per_page: this.perPage };
            if (this.searchTerm) params.search = this.searchTerm;
            
            const response = await API.get('/admin/materials', params);
            this.renderContent(response.data);
        } catch (error) {
            console.error('Materials error:', error);
            Toast.error('Error', 'Failed to load materials');
        }
    }

    renderContent(data) {
        const canWrite = State.canWrite();
        
        this.container.innerHTML = `
            <div class="toolbar">
                <div class="toolbar-left">
                    <input type="text" id="search-input" placeholder="Search materials..." value="${this.searchTerm}"
                        style="padding: var(--spacing-2) var(--spacing-3); border: 1px solid var(--color-border); border-radius: var(--border-radius-lg); width: 300px;">
                </div>
                <div class="toolbar-right">
                    ${canWrite ? '<button class="btn btn-primary" id="add-btn">Add Material</button>' : ''}
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(data.data || []).map(item => `
                                <tr>
                                    <td><strong>${item.name}</strong></td>
                                    <td>${item.sku || '-'}</td>
                                    <td>${item.category || '-'}</td>
                                    <td>${formatCurrency(item.unit_price)}</td>
                                    <td>${item.stock_quantity || 0}</td>
                                    <td><span class="badge ${item.is_active ? 'badge-success' : 'badge-secondary'}">${item.is_active ? 'Active' : 'Inactive'}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            ${canWrite ? `<button class="btn btn-sm btn-secondary" onclick="window.editMaterial(${item.id})">Edit</button>` : ''}
                                            ${State.canDelete() ? `<button class="btn btn-sm btn-danger" onclick="window.deleteMaterial(${item.id})">Delete</button>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        this.attachEventListeners();
    }

    attachEventListeners() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.render();
            }, 500));
        }

        const addBtn = document.getElementById('add-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showForm());
        }

        window.editMaterial = (id) => this.showForm(id);
        window.deleteMaterial = (id) => this.delete(id);
    }

    async showForm(id = null) {
        const content = '<form id="material-form"><div class="form-group"><label>Name *</label><input type="text" name="name" required></div><div class="form-group"><label>Unit Price *</label><input type="number" name="unit_price" step="0.01" required></div></form>';
        
        const modal = new Modal({
            title: id ? 'Edit Material' : 'Add Material',
            content,
            onConfirm: async () => {
                const form = document.getElementById('material-form');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                
                if (id) {
                    await API.put(`/admin/materials/${id}`, data);
                } else {
                    await API.post('/admin/materials', data);
                }
                Toast.success('Success', 'Material saved');
                this.render();
            }
        });
        
        await modal.show();
    }

    async delete(id) {
        try {
            await Modal.confirm('Delete', 'Are you sure?');
            await API.delete(`/admin/materials/${id}`);
            Toast.success('Success', 'Material deleted');
            this.render();
        } catch (error) {
            if (error !== false) Toast.error('Error', 'Failed to delete');
        }
    }
}
