import { API } from '../api.js';
import { Toast } from '../components/toast.js';
import { formatCurrency, formatNumber, formatDateTime } from '../utils/helpers.js';

export class DashboardView {
    constructor() {
        this.container = document.getElementById('admin-content');
        this.charts = {};
    }

    async render() {
        this.container.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        `;

        try {
            const [dashboardData, recentActivity] = await Promise.all([
                API.get('/admin/analytics/dashboard'),
                API.get('/admin/audit-logs', { per_page: 5 })
            ]);

            this.renderContent(dashboardData.data, recentActivity.data);
        } catch (error) {
            console.error('Dashboard error:', error);
            Toast.error('Error', 'Failed to load dashboard data');
            this.container.innerHTML = '<div class="empty-state"><p>Failed to load dashboard</p></div>';
        }
    }

    renderContent(data, activityData) {
        this.container.innerHTML = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Requests</div>
                        <div class="stat-value">${formatNumber(data.total_requests || 0)}</div>
                        <div class="stat-change positive">+${data.requests_change || 0}% this month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Cost Estimates</div>
                        <div class="stat-value">${formatNumber(data.total_estimates || 0)}</div>
                        <div class="stat-change positive">+${data.estimates_change || 0}% this month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">${formatCurrency(data.total_revenue || 0)}</div>
                        <div class="stat-change positive">+${data.revenue_change || 0}% this month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon error">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Conversion Rate</div>
                        <div class="stat-value">${formatNumber(data.conversion_rate || 0, 1)}%</div>
                        <div class="stat-change ${data.conversion_change >= 0 ? 'positive' : 'negative'}">
                            ${data.conversion_change >= 0 ? '+' : ''}${data.conversion_change || 0}% this month
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-6); margin-bottom: var(--spacing-6);">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Submissions Over Time</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="submissions-chart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top Services</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="services-chart" width="300" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-6);">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Request Status</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="status-chart" width="300" height="200"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="card-body">
                        ${this.renderRecentActivity(activityData)}
                    </div>
                </div>
            </div>
        `;

        this.renderCharts(data);
    }

    renderRecentActivity(activityData) {
        if (!activityData || !activityData.data || activityData.data.length === 0) {
            return '<p class="text-secondary">No recent activity</p>';
        }

        return `
            <div style="display: flex; flex-direction: column; gap: var(--spacing-3);">
                ${activityData.data.map(activity => `
                    <div style="padding: var(--spacing-3); background: var(--color-bg-secondary); border-radius: var(--border-radius-lg);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-1);">
                            <strong>${activity.admin_username || 'Admin'}</strong>
                            <span style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">
                                ${formatDateTime(activity.created_at)}
                            </span>
                        </div>
                        <div style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">
                            ${activity.action} - ${activity.resource_type}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderCharts(data) {
        this.renderSubmissionsChart(data.submissions_over_time || []);
        this.renderServicesChart(data.top_services || []);
        this.renderStatusChart(data.request_status || {});
    }

    renderSubmissionsChart(submissionsData) {
        const ctx = document.getElementById('submissions-chart');
        if (!ctx) return;

        const labels = submissionsData.map(item => item.date || item.label);
        const values = submissionsData.map(item => item.count || item.value || 0);

        if (this.charts.submissions) {
            this.charts.submissions.destroy();
        }

        this.charts.submissions = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Submissions',
                    data: values,
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    renderServicesChart(servicesData) {
        const ctx = document.getElementById('services-chart');
        if (!ctx) return;

        const labels = servicesData.map(item => item.name || item.label);
        const values = servicesData.map(item => item.count || item.value || 0);

        if (this.charts.services) {
            this.charts.services.destroy();
        }

        this.charts.services = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgb(37, 99, 235)',
                        'rgb(124, 58, 237)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    renderStatusChart(statusData) {
        const ctx = document.getElementById('status-chart');
        if (!ctx) return;

        const labels = Object.keys(statusData);
        const values = Object.values(statusData);

        if (this.charts.status) {
            this.charts.status.destroy();
        }

        this.charts.status = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Requests',
                    data: values,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
}
