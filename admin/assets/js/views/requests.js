import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { State } from '../state.js';
import { formatDateTime, debounce } from '../utils/helpers.js';

export class RequestsView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.currentPage = 1;
        this.filters = { status: '' };
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const params = { page: this.currentPage, ...this.filters };
            const response = await API.get('/admin/requests', params);
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load requests');
        }
    }

    renderContent(data) {
        const canWrite = State.canWrite();
        
        this.container.innerHTML = `
            <div class="filter-bar">
                <div class="filter-item">
                    <select id="status-filter" style="width: 100%; padding: var(--spacing-2) var(--spacing-3); border: 1px solid var(--color-border); border-radius: var(--border-radius-lg);">
                        <option value="">All Statuses</option>
                        <option value="pending" ${this.filters.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="in_progress" ${this.filters.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="completed" ${this.filters.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${this.filters.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(data.data || []).map(item => `
                                <tr>
                                    <td>#${item.id}</td>
                                    <td>${item.name}</td>
                                    <td>${item.email}</td>
                                    <td>${item.service_name || '-'}</td>
                                    <td>
                                        <select class="status-select" data-id="${item.id}" ${!canWrite ? 'disabled' : ''}>
                                            <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="in_progress" ${item.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="completed" ${item.status === 'completed' ? 'selected' : ''}>Completed</option>
                                            <option value="cancelled" ${item.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>${formatDateTime(item.created_at)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="window.viewRequest(${item.id})">View</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filters.status = e.target.value;
                this.currentPage = 1;
                this.render();
            });
        }

        if (canWrite) {
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', (e) => this.updateStatus(e.target.dataset.id, e.target.value));
            });
        }

        window.viewRequest = (id) => this.viewRequest(id);
    }

    async updateStatus(id, status) {
        try {
            await API.put(`/admin/requests/${id}`, { status });
            Toast.success('Success', 'Status updated');
        } catch (error) {
            Toast.error('Error', 'Failed to update status');
            this.render();
        }
    }

    async viewRequest(id) {
        try {
            const response = await API.get(`/admin/requests/${id}`);
            const request = response.data;
            
            alert(`Request #${id}\nName: ${request.name}\nEmail: ${request.email}\nMessage: ${request.message}`);
        } catch (error) {
            Toast.error('Error', 'Failed to load request details');
        }
    }
}
