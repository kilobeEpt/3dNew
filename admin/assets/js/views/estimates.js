import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { formatCurrency, formatDateTime } from '../utils/helpers.js';

export class EstimatesView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.currentPage = 1;
    }

    async render() {
        this.container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            const response = await API.get('/admin/estimates', { page: this.currentPage });
            this.renderContent(response.data);
        } catch (error) {
            Toast.error('Error', 'Failed to load estimates');
        }
    }

    renderContent(data) {
        this.container.innerHTML = `
            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Service</th>
                                <th>Total Cost</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(data.data || []).map(item => `
                                <tr>
                                    <td>#${item.id}</td>
                                    <td>${item.customer_name || '-'}</td>
                                    <td>${item.customer_email || '-'}</td>
                                    <td>${item.service_name || '-'}</td>
                                    <td>${formatCurrency(item.total_cost)}</td>
                                    <td>${formatDateTime(item.created_at)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="window.viewEstimate(${item.id})">View</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        window.viewEstimate = (id) => this.viewEstimate(id);
    }

    async viewEstimate(id) {
        try {
            const response = await API.get(`/admin/estimates/${id}`);
            const estimate = response.data;
            
            alert(`Estimate #${id}\nCustomer: ${estimate.customer_name}\nTotal: ${formatCurrency(estimate.total_cost)}`);
        } catch (error) {
            Toast.error('Error', 'Failed to load estimate');
        }
    }
}
