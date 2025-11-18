import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { formatDateTime, debounce } from '../utils/helpers.js';

export class AuditLogsView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.currentPage = 1;
        this.filters = { action: '', resource_type: '' };
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const params = { page: this.currentPage, per_page: 50, ...this.filters };
            const response = await API.get('/admin/audit-logs', params);
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load audit logs');
        }
    }

    renderContent(data) {
        this.container.innerHTML = `
            <div class="filter-bar">
                <div class="filter-item">
                    <select id="action-filter" style="width: 100%; padding: var(--spacing-2) var(--spacing-3); border: 1px solid var(--color-border); border-radius: var(--border-radius-lg);">
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select id="resource-filter" style="width: 100%; padding: var(--spacing-2) var(--spacing-3); border: 1px solid var(--color-border); border-radius: var(--border-radius-lg);">
                        <option value="">All Resources</option>
                        <option value="service">Services</option>
                        <option value="material">Materials</option>
                        <option value="gallery">Gallery</option>
                        <option value="news">News</option>
                        <option value="settings">Settings</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(data.data || []).map(log => `
                                <tr>
                                    <td><strong>${log.admin_username || 'Unknown'}</strong></td>
                                    <td><span class="badge badge-primary">${log.action}</span></td>
                                    <td>${log.resource_type} ${log.resource_id ? `#${log.resource_id}` : ''}</td>
                                    <td>${log.ip_address || '-'}</td>
                                    <td>${formatDateTime(log.created_at)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        const actionFilter = document.getElementById('action-filter');
        if (actionFilter) {
            actionFilter.addEventListener('change', (e) => {
                this.filters.action = e.target.value;
                this.currentPage = 1;
                this.render();
            });
        }

        const resourceFilter = document.getElementById('resource-filter');
        if (resourceFilter) {
            resourceFilter.addEventListener('change', (e) => {
                this.filters.resource_type = e.target.value;
                this.currentPage = 1;
                this.render();
            });
        }
    }
}
