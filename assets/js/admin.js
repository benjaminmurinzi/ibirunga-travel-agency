// Admin Dashboard JavaScript

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Reset form if exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const formAction = document.getElementById('formAction');
            if (formAction) formAction.value = 'create';
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.textContent = 'Add New';
        }
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.createElement('button');
    sidebarToggle.className = 'sidebar-toggle';
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    sidebarToggle.style.cssText = `
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: var(--admin-primary);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
    `;
    
    document.body.appendChild(sidebarToggle);
    
    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    });
    
    // Show toggle button on mobile
    if (window.innerWidth <= 768) {
        sidebarToggle.style.display = 'block';
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        } else {
            sidebarToggle.style.display = 'none';
            document.querySelector('.sidebar')?.classList.remove('active');
        }
    });
    
    // Active menu item
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });
    
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});

// Confirm delete
function confirmDelete(itemName) {
    return confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`);
}

// Data table search
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Sort table
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    let ascending = true;
    const header = table.querySelectorAll('th')[columnIndex];
    
    if (header.classList.contains('sort-asc')) {
        ascending = false;
        header.classList.remove('sort-asc');
        header.classList.add('sort-desc');
    } else {
        table.querySelectorAll('th').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add('sort-asc');
    }
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return ascending ? aValue - bValue : bValue - aValue;
        }
        
        return ascending 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Image preview
function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Add image preview functionality to file inputs
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.nextElementSibling;
                    
                    if (!preview || !preview.classList.contains('image-preview')) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        preview.style.cssText = `
                            max-width: 200px;
                            max-height: 200px;
                            margin-top: 10px;
                            border-radius: 8px;
                        `;
                        input.parentNode.appendChild(preview);
                    }
                    
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});

// Bulk actions
function selectAllCheckboxes(masterCheckbox, className) {
    const checkboxes = document.querySelectorAll(`.${className}`);
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const checked = document.querySelectorAll('input[type="checkbox"]:checked').length;
    const bulkBar = document.getElementById('bulkActionsBar');
    
    if (bulkBar) {
        bulkBar.style.display = checked > 0 ? 'flex' : 'none';
        const count = bulkBar.querySelector('.selected-count');
        if (count) count.textContent = `${checked} selected`;
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        
        cols.forEach(col => {
            csvRow.push(`"${col.textContent.replace(/"/g, '""')}"`);
        });
        
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ECC71' : '#E74C3C'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// AJAX form submission
function submitFormAjax(formId, successCallback) {
    const form = document.getElementById(formId);
    
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message || 'Operation successful', 'success');
                if (successCallback) successCallback(data);
            } else {
                showNotification(data.message || 'Operation failed', 'error');
            }
        } catch (error) {
            showNotification('An error occurred. Please try again.', 'error');
            console.error('Form submission error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Real-time validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateInput(this);
            }
        });
    });
}

function validateInput(input) {
    const value = input.value.trim();
    let isValid = true;
    let message = '';
    
    if (input.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required';
    } else if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email';
        }
    } else if (input.type === 'url' && value) {
        try {
            new URL(value);
        } catch {
            isValid = false;
            message = 'Please enter a valid URL';
        }
    } else if (input.type === 'number' && value) {
        const min = input.getAttribute('min');
        const max = input.getAttribute('max');
        const num = parseFloat(value);
        
        if (min && num < parseFloat(min)) {
            isValid = false;
            message = `Value must be at least ${min}`;
        } else if (max && num > parseFloat(max)) {
            isValid = false;
            message = `Value must not exceed ${max}`;
        }
    }
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        removeErrorMessage(input);
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        showErrorMessage(input, message);
    }
    
    return isValid;
}

function showErrorMessage(input, message) {
    removeErrorMessage(input);
    const error = document.createElement('div');
    error.className = 'error-message';
    error.textContent = message;
    error.style.cssText = 'color: #E74C3C; font-size: 0.85rem; margin-top: 0.3rem;';
    input.parentNode.appendChild(error);
}

function removeErrorMessage(input) {
    const error = input.parentNode.querySelector('.error-message');
    if (error) error.remove();
}

// Statistics counter animation
function animateCounter(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = Math.round(target);
            clearInterval(timer);
        } else {
            element.textContent = Math.round(current);
        }
    }, 16);
}

// Initialize counters on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-details h3').forEach(counter => {
        const target = parseInt(counter.textContent.replace(/,/g, ''));
        if (!isNaN(target)) {
            counter.textContent = '0';
            animateCounter(counter, target);
        }
    });
});

// Date range picker helper
function initDateRangePicker(startId, endId) {
    const startInput = document.getElementById(startId);
    const endInput = document.getElementById(endId);
    
    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            endInput.min = this.value;
        });
        
        endInput.addEventListener('change', function() {
            startInput.max = this.value;
        });
    }
}

// Copy to clipboard
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    });
}

// Add loading overlay
function showLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.remove();
}
