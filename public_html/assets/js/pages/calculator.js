import { api } from '../api.js';

const STORAGE_KEY = 'calculator_inputs';
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_FILE_TYPES = ['.stl', '.obj', '.3mf', '.step', '.stp'];

class CostCalculator {
    constructor() {
        this.materials = [];
        this.pricingRules = [];
        this.currentUnit = 'mm';
        this.uploadedFile = null;
        this.taxRate = 0;
        this.sessionId = this.getOrCreateSessionId();
        
        this.init();
    }

    async init() {
        await this.loadData();
        this.setupEventListeners();
        this.loadSavedInputs();
        this.checkOnlineStatus();
        this.logAnalyticsEvent('calculator_view');
        
        setTimeout(() => {
            document.getElementById('summaryLoading').style.display = 'none';
            document.getElementById('summaryContent').style.display = 'flex';
            this.calculateTotal();
        }, 800);
    }

    async loadData() {
        try {
            const [materialsResponse, pricingResponse, settingsResponse] = await Promise.all([
                api.getMaterials({ per_page: 100 }),
                api.get('/pricing-rules'),
                api.getSettings()
            ]);

            this.materials = materialsResponse.data?.data || [];
            this.pricingRules = pricingResponse.data || [];
            this.taxRate = settingsResponse.data?.tax_rate || 0;

            this.populateMaterials();
            this.updateTaxRateDisplay();
        } catch (error) {
            console.error('Failed to load data:', error);
            this.showError('Failed to load calculator data. Please refresh the page.');
        }
    }

    populateMaterials() {
        const materialSelect = document.getElementById('material');
        materialSelect.innerHTML = '<option value="">Select a material...</option>';

        this.materials.forEach(material => {
            const option = document.createElement('option');
            option.value = material.id;
            option.textContent = `${material.name} - $${material.unit_price}/${material.unit}`;
            option.dataset.price = material.unit_price;
            option.dataset.unit = material.unit;
            option.dataset.name = material.name;
            materialSelect.appendChild(option);
        });
    }

    setupEventListeners() {
        const form = document.getElementById('calculatorForm');
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (input.type !== 'file') {
                input.addEventListener('input', () => {
                    this.calculateTotal();
                    this.saveInputs();
                });
                input.addEventListener('change', () => {
                    this.calculateTotal();
                    this.saveInputs();
                });
            }
        });

        document.getElementById('infill').addEventListener('input', (e) => {
            document.getElementById('infillValue').textContent = e.target.value;
            this.updateInfillGradient(e.target.value);
        });

        document.querySelectorAll('.unit-toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchUnit(btn.dataset.unit);
            });
        });

        const fileInput = document.getElementById('modelFile');
        fileInput.addEventListener('change', (e) => this.handleFileUpload(e));

        document.getElementById('removeFile').addEventListener('click', () => {
            this.removeFile();
        });

        const fileUploadArea = document.getElementById('fileUploadArea');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, () => {
                fileUploadArea.style.borderColor = 'var(--color-primary)';
                fileUploadArea.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, () => {
                fileUploadArea.style.borderColor = '';
                fileUploadArea.style.backgroundColor = '';
            });
        });

        fileUploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                this.handleFileUpload({ target: fileInput });
            }
        });

        form.addEventListener('submit', (e) => this.handleSubmit(e));

        document.getElementById('clearBtn').addEventListener('click', () => {
            this.clearForm();
        });

        document.getElementById('printSummary').addEventListener('click', () => {
            this.printSummary();
        });

        document.getElementById('downloadPDF').addEventListener('click', () => {
            this.downloadPDF();
        });

        document.getElementById('modalClose').addEventListener('click', () => {
            this.closeModal();
        });

        document.getElementById('modalOverlay').addEventListener('click', () => {
            this.closeModal();
        });

        document.getElementById('modalOkBtn').addEventListener('click', () => {
            this.closeModal();
        });

        document.getElementById('newEstimateBtn').addEventListener('click', () => {
            this.closeModal();
            this.clearForm();
        });

        window.addEventListener('online', () => {
            this.checkOnlineStatus();
        });

        window.addEventListener('offline', () => {
            this.checkOnlineStatus();
        });
    }

    switchUnit(unit) {
        if (this.currentUnit === unit) return;

        const conversionFactor = unit === 'cm' ? 0.1 : 10;
        const dimensionInputs = ['width', 'height', 'length'];

        dimensionInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input.value) {
                input.value = (parseFloat(input.value) * conversionFactor).toFixed(2);
            }
        });

        this.currentUnit = unit;

        document.querySelectorAll('.unit-toggle-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.unit === unit);
        });

        document.querySelectorAll('.current-unit').forEach(span => {
            span.textContent = unit;
        });

        this.calculateTotal();
    }

    updateInfillGradient(value) {
        const range = document.getElementById('infill');
        const percentage = value;
        range.style.background = `linear-gradient(to right, var(--color-primary) 0%, var(--color-primary) ${percentage}%, var(--color-border) ${percentage}%, var(--color-border) 100%)`;
    }

    calculateTotal() {
        const material = document.getElementById('material');
        const quality = document.getElementById('quality').value;
        const infill = parseFloat(document.getElementById('infill').value) || 0;
        const width = parseFloat(document.getElementById('width').value) || 0;
        const height = parseFloat(document.getElementById('height').value) || 0;
        const length = parseFloat(document.getElementById('length').value) || 0;
        const finishing = document.getElementById('finishing');
        const quantity = parseInt(document.getElementById('quantity').value) || 1;

        let materialCost = 0;
        let timeCost = 0;
        let finishingCost = parseFloat(finishing.selectedOptions[0]?.dataset.price || 0);

        if (material.value && width > 0 && height > 0 && length > 0) {
            const materialPrice = parseFloat(material.selectedOptions[0]?.dataset.price || 0);
            
            const widthMM = this.currentUnit === 'cm' ? width * 10 : width;
            const heightMM = this.currentUnit === 'cm' ? height * 10 : height;
            const lengthMM = this.currentUnit === 'cm' ? length * 10 : length;
            
            const volumeCM3 = (widthMM * heightMM * lengthMM) / 1000;
            document.getElementById('volumeDisplay').textContent = volumeCM3.toFixed(2);
            
            const materialVolume = volumeCM3 * (infill / 100);
            const materialDensity = 1.24; // g/cm³ for PLA (default)
            const materialWeight = materialVolume * materialDensity;
            
            materialCost = (materialWeight / 1000) * materialPrice;

            const qualityMultipliers = {
                draft: 1.0,
                standard: 1.3,
                high: 1.8
            };
            const qualityMultiplier = qualityMultipliers[quality] || 1.0;
            
            const printTimeHours = (volumeCM3 / 10) * qualityMultiplier * (infill / 50);
            const hourlyRate = 15;
            timeCost = printTimeHours * hourlyRate;

            const printTimeFormatted = this.formatPrintTime(printTimeHours);
            document.getElementById('printTime').textContent = printTimeFormatted;
            document.getElementById('materialUsed').textContent = `${materialWeight.toFixed(1)}g`;
        } else {
            document.getElementById('printTime').textContent = '-';
            document.getElementById('materialUsed').textContent = '-';
            document.getElementById('volumeDisplay').textContent = '0';
        }

        const subtotalPerUnit = materialCost + timeCost + finishingCost;
        const subtotal = subtotalPerUnit * quantity;

        let discount = 0;
        if (quantity >= 10 && quantity < 50) {
            discount = subtotal * 0.10;
        } else if (quantity >= 50) {
            discount = subtotal * 0.20;
        }

        const subtotalAfterDiscount = subtotal - discount;
        const taxAmount = subtotalAfterDiscount * (this.taxRate / 100);
        const total = subtotalAfterDiscount + taxAmount;

        document.getElementById('materialCost').textContent = this.formatCurrency(materialCost * quantity);
        document.getElementById('timeCost').textContent = this.formatCurrency(timeCost * quantity);
        document.getElementById('finishingCost').textContent = this.formatCurrency(finishingCost * quantity);
        document.getElementById('quantitySummary').textContent = quantity.toString();
        document.getElementById('subtotal').textContent = this.formatCurrency(subtotalAfterDiscount);
        document.getElementById('taxAmount').textContent = this.formatCurrency(taxAmount);
        document.getElementById('total').textContent = this.formatCurrency(total);

        const enableButtons = total > 0;
        document.getElementById('printSummary').disabled = !enableButtons;
        document.getElementById('downloadPDF').disabled = !enableButtons;
    }

    formatPrintTime(hours) {
        if (hours < 1) {
            return `${Math.round(hours * 60)} minutes`;
        } else {
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            return m > 0 ? `${h}h ${m}m` : `${h}h`;
        }
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    updateTaxRateDisplay() {
        document.getElementById('taxRate').textContent = this.taxRate.toFixed(1);
    }

    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!ALLOWED_FILE_TYPES.includes(fileExtension)) {
            this.showFileError('Invalid file type. Please upload an STL, OBJ, 3MF, or STEP file.');
            event.target.value = '';
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            this.showFileError('File is too large. Maximum size is 5MB.');
            event.target.value = '';
            return;
        }

        this.uploadedFile = file;
        this.showFilePreview(file);
        this.hideFileError();
        
        this.logAnalyticsEvent('calculator_file_upload', {
            file_type: fileExtension,
            file_size: file.size
        });
    }

    showFilePreview(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = this.formatFileSize(file.size);
        document.getElementById('fileUploadArea').style.display = 'none';
        document.getElementById('filePreview').style.display = 'flex';
    }

    removeFile() {
        this.uploadedFile = null;
        document.getElementById('modelFile').value = '';
        document.getElementById('fileUploadArea').style.display = 'flex';
        document.getElementById('filePreview').style.display = 'none';
        this.hideFileError();
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    showFileError(message) {
        const errorElement = document.getElementById('fileError');
        errorElement.textContent = message;
        errorElement.classList.add('visible');
    }

    hideFileError() {
        const errorElement = document.getElementById('fileError');
        errorElement.textContent = '';
        errorElement.classList.remove('visible');
    }

    async handleSubmit(event) {
        event.preventDefault();

        if (!navigator.onLine) {
            this.showError('You are offline. Please check your connection and try again.');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnSpinner = submitBtn.querySelector('.btn-spinner');
        
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline-flex';

        try {
            const formData = await this.prepareFormData();
            
            this.logAnalyticsEvent('calculator_submit_attempt', {
                has_file: !!this.uploadedFile,
                total_amount: this.parseAmountFromElement('total')
            });

            const response = await this.submitEstimate(formData);

            this.logAnalyticsEvent('calculator_submit_success', {
                estimate_number: response.data.estimate_number,
                estimate_id: response.data.estimate_id,
                total_amount: response.data.total_amount
            });

            this.showSuccessModal(response.data);
            this.clearSavedInputs();

        } catch (error) {
            console.error('Submit error:', error);
            
            this.logAnalyticsEvent('calculator_submit_error', {
                error_message: error.message
            });

            const errorMessage = error.data?.message || error.message || 'Failed to submit estimate. Please try again.';
            this.showError(errorMessage);
        } finally {
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnSpinner.style.display = 'none';
        }
    }

    async prepareFormData() {
        const material = document.getElementById('material');
        const quality = document.getElementById('quality').value;
        const infill = parseFloat(document.getElementById('infill').value);
        const width = parseFloat(document.getElementById('width').value);
        const height = parseFloat(document.getElementById('height').value);
        const length = parseFloat(document.getElementById('length').value);
        const finishing = document.getElementById('finishing').value;
        const quantity = parseInt(document.getElementById('quantity').value);

        const materialPrice = parseFloat(material.selectedOptions[0]?.dataset.price || 0);
        const materialName = material.selectedOptions[0]?.dataset.name || '';
        
        const widthMM = this.currentUnit === 'cm' ? width * 10 : width;
        const heightMM = this.currentUnit === 'cm' ? height * 10 : height;
        const lengthMM = this.currentUnit === 'cm' ? length * 10 : length;
        
        const volumeCM3 = (widthMM * heightMM * lengthMM) / 1000;
        const materialVolume = volumeCM3 * (infill / 100);
        const materialWeight = materialVolume * 1.24;
        
        const materialCost = (materialWeight / 1000) * materialPrice;

        const qualityMultipliers = { draft: 1.0, standard: 1.3, high: 1.8 };
        const qualityMultiplier = qualityMultipliers[quality] || 1.0;
        const printTimeHours = (volumeCM3 / 10) * qualityMultiplier * (infill / 50);
        const timeCost = printTimeHours * 15;

        const finishingSelect = document.getElementById('finishing');
        const finishingCost = parseFloat(finishingSelect.selectedOptions[0]?.dataset.price || 0);

        const subtotalPerUnit = materialCost + timeCost + finishingCost;
        const subtotal = subtotalPerUnit * quantity;

        let discount = 0;
        if (quantity >= 10 && quantity < 50) {
            discount = subtotal * 0.10;
        } else if (quantity >= 50) {
            discount = subtotal * 0.20;
        }

        const subtotalAfterDiscount = subtotal - discount;
        const taxAmount = subtotalAfterDiscount * (this.taxRate / 100);
        const total = subtotalAfterDiscount + taxAmount;

        const csrfToken = await this.getCsrfToken();
        const captchaToken = 'bypass_for_calculator'; // In production, use real CAPTCHA

        const calculatorData = {
            material_id: parseInt(material.value),
            material_name: materialName,
            quality: quality,
            infill: infill,
            dimensions: {
                width: width,
                height: height,
                length: length,
                unit: this.currentUnit
            },
            volume_cm3: volumeCM3,
            weight_grams: materialWeight,
            finishing: finishing,
            quantity: quantity,
            print_time_hours: printTimeHours
        };

        const items = [
            {
                item_type: 'material',
                item_id: parseInt(material.value),
                description: `${materialName} (${materialWeight.toFixed(1)}g)`,
                quantity: quantity,
                unit: 'piece',
                unit_price: materialCost
            },
            {
                item_type: 'custom',
                description: `Print Time (${quality} quality, ${infill}% infill)`,
                quantity: quantity,
                unit: 'piece',
                unit_price: timeCost
            }
        ];

        if (finishingCost > 0) {
            items.push({
                item_type: 'custom',
                description: `Post-Processing: ${finishing}`,
                quantity: quantity,
                unit: 'piece',
                unit_price: finishingCost
            });
        }

        let fileData = null;
        if (this.uploadedFile) {
            fileData = await this.fileToBase64(this.uploadedFile);
        }

        return {
            customer_name: document.getElementById('customerName').value,
            customer_email: document.getElementById('customerEmail').value,
            customer_phone: document.getElementById('customerPhone').value || null,
            title: `3D Print: ${materialName} - ${width}x${height}x${length}${this.currentUnit}`,
            description: `Quality: ${quality}, Infill: ${infill}%, Finishing: ${finishing}`,
            items: items,
            subtotal: subtotalAfterDiscount,
            tax_rate: this.taxRate,
            tax_amount: taxAmount,
            discount_amount: discount,
            total_amount: total,
            currency: 'USD',
            notes: document.getElementById('notes').value || null,
            calculator_data: calculatorData,
            file_data: fileData,
            file_name: this.uploadedFile ? this.uploadedFile.name : null,
            source: 'calculator',
            captcha_token: captchaToken,
            csrf_token: csrfToken
        };
    }

    async fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    async getCsrfToken() {
        try {
            const response = await api.getCsrfToken();
            return response.data?.csrf_token || '';
        } catch (error) {
            console.error('Failed to get CSRF token:', error);
            return '';
        }
    }

    async submitEstimate(data) {
        return await api.submitCostEstimate(data);
    }

    showSuccessModal(data) {
        document.getElementById('estimateNumber').textContent = data.estimate_number;
        document.getElementById('estimateTotal').textContent = this.formatCurrency(data.total_amount);
        document.getElementById('confirmationModal').style.display = 'flex';
    }

    closeModal() {
        document.getElementById('confirmationModal').style.display = 'none';
    }

    clearForm() {
        document.getElementById('calculatorForm').reset();
        this.removeFile();
        this.currentUnit = 'mm';
        document.querySelectorAll('.unit-toggle-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.unit === 'mm');
        });
        document.querySelectorAll('.current-unit').forEach(span => {
            span.textContent = 'mm';
        });
        document.getElementById('infillValue').textContent = '20';
        this.updateInfillGradient(20);
        this.calculateTotal();
        this.clearSavedInputs();
        
        this.logAnalyticsEvent('calculator_clear');
    }

    saveInputs() {
        const inputs = {
            material: document.getElementById('material').value,
            quality: document.getElementById('quality').value,
            infill: document.getElementById('infill').value,
            width: document.getElementById('width').value,
            height: document.getElementById('height').value,
            length: document.getElementById('length').value,
            finishing: document.getElementById('finishing').value,
            quantity: document.getElementById('quantity').value,
            unit: this.currentUnit,
            customerName: document.getElementById('customerName').value,
            customerEmail: document.getElementById('customerEmail').value,
            customerPhone: document.getElementById('customerPhone').value,
            notes: document.getElementById('notes').value
        };

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(inputs));
        } catch (error) {
            console.error('Failed to save inputs:', error);
        }
    }

    loadSavedInputs() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved) return;

            const inputs = JSON.parse(saved);

            if (inputs.material) document.getElementById('material').value = inputs.material;
            if (inputs.quality) document.getElementById('quality').value = inputs.quality;
            if (inputs.infill) {
                document.getElementById('infill').value = inputs.infill;
                document.getElementById('infillValue').textContent = inputs.infill;
                this.updateInfillGradient(inputs.infill);
            }
            if (inputs.width) document.getElementById('width').value = inputs.width;
            if (inputs.height) document.getElementById('height').value = inputs.height;
            if (inputs.length) document.getElementById('length').value = inputs.length;
            if (inputs.finishing) document.getElementById('finishing').value = inputs.finishing;
            if (inputs.quantity) document.getElementById('quantity').value = inputs.quantity;
            if (inputs.unit && inputs.unit !== 'mm') this.switchUnit(inputs.unit);
            if (inputs.customerName) document.getElementById('customerName').value = inputs.customerName;
            if (inputs.customerEmail) document.getElementById('customerEmail').value = inputs.customerEmail;
            if (inputs.customerPhone) document.getElementById('customerPhone').value = inputs.customerPhone;
            if (inputs.notes) document.getElementById('notes').value = inputs.notes;

        } catch (error) {
            console.error('Failed to load saved inputs:', error);
        }
    }

    clearSavedInputs() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            console.error('Failed to clear saved inputs:', error);
        }
    }

    printSummary() {
        window.print();
        this.logAnalyticsEvent('calculator_print_summary');
    }

    downloadPDF() {
        this.showError('PDF download feature coming soon!');
        this.logAnalyticsEvent('calculator_download_pdf');
    }

    parseAmountFromElement(elementId) {
        const text = document.getElementById(elementId).textContent;
        return parseFloat(text.replace(/[^0-9.-]+/g, ''));
    }

    checkOnlineStatus() {
        const offlineMessage = document.getElementById('offlineMessage');
        if (navigator.onLine) {
            offlineMessage.style.display = 'none';
        } else {
            offlineMessage.style.display = 'block';
        }
    }

    showError(message) {
        alert(message);
    }

    getOrCreateSessionId() {
        let sessionId = sessionStorage.getItem('calculator_session_id');
        if (!sessionId) {
            sessionId = this.generateSessionId();
            sessionStorage.setItem('calculator_session_id', sessionId);
        }
        return sessionId;
    }

    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    logAnalyticsEvent(eventType, eventData = {}) {
        const analyticsData = {
            event_type: eventType,
            event_category: 'calculator',
            event_data: eventData,
            user_session_id: this.sessionId,
            page_url: window.location.href,
            referrer: document.referrer,
            timestamp: new Date().toISOString()
        };

        try {
            api.post('/analytics/events', analyticsData).catch(err => {
                console.debug('Analytics logging failed:', err);
            });
        } catch (error) {
            console.debug('Analytics event error:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new CostCalculator();
});
