/**
 * ActiveCore Gym - Main JavaScript File
 * Provides common functionality for the gym management system
 */

// ===== GLOBAL VARIABLES =====
const BASE_URL = window.location.origin + '/activecore_gym/';
let currentUserId = null;
let currentUserType = null;

// ===== UTILITY FUNCTIONS =====

/**
 * Format date to readable string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format time to readable string
 */
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

/**
 * Sanitize input to prevent XSS
 */
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

/**
 * Show loading state
 */
function showLoading(element) {
    element.classList.add('loading');
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    element.classList.remove('loading');
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

/**
 * Confirm action with modal
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Make AJAX request
 */
async function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('AJAX request failed:', error);
        showToast('Request failed. Please try again.', 'danger');
        throw error;
    }
}

// ===== FORM VALIDATION =====

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    const minLength = 6;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    
    const strength = {
        length: password.length >= minLength,
        upperCase: hasUpperCase,
        lowerCase: hasLowerCase,
        numbers: hasNumbers,
        score: 0
    };
    
    // Calculate strength score
    if (strength.length) strength.score += 25;
    if (strength.upperCase) strength.score += 25;
    if (strength.lowerCase) strength.score += 25;
    if (strength.numbers) strength.score += 25;
    
    return strength;
}

/**
 * Show password strength indicator
 */
function showPasswordStrength(password, inputElement) {
    const strength = validatePassword(password);
    const strengthBar = document.getElementById('passwordStrength');
    
    if (strengthBar) {
        strengthBar.style.width = strength.score + '%';
        
        if (strength.score < 50) {
            strengthBar.className = 'password-strength bg-danger';
        } else if (strength.score < 75) {
            strengthBar.className = 'password-strength bg-warning';
        } else {
            strengthBar.className = 'password-strength bg-success';
        }
    }
    
    return strength;
}

// ===== SESSION MANAGEMENT =====

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return document.cookie.includes('user_id') || localStorage.getItem('user_id');
}

/**
 * Get current user info
 */
function getCurrentUser() {
    const userInfo = localStorage.getItem('userInfo');
    return userInfo ? JSON.parse(userInfo) : null;
}

/**
 * Logout user
 */
function logout() {
    localStorage.clear();
    document.cookie.split(";").forEach(function(c) { 
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
    });
    window.location.href = BASE_URL + 'views/auth/login.php';
}

// ===== DYNAMIC CONTENT =====

/**
 * Load content dynamically
 */
async function loadContent(url, targetElement) {
    showLoading(targetElement);
    
    try {
        const response = await fetch(url);
        const html = await response.text();
        targetElement.innerHTML = html;
    } catch (error) {
        console.error('Failed to load content:', error);
        showToast('Failed to load content', 'danger');
    } finally {
        hideLoading(targetElement);
    }
}

/**
 * Filter table rows
 */
function filterTable(tableId, searchInput) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const filter = searchInput.value.toLowerCase();
    
    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let cell of cells) {
            if (cell.textContent.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

/**
 * Sort table
 */
function sortTable(tableId, columnIndex, ascending = true) {
    const table = document.getElementById(tableId);
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.getElementsByTagName('td')[columnIndex].textContent.trim();
        const bValue = b.getElementsByTagName('td')[columnIndex].textContent.trim();
        
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return ascending ? parseFloat(aValue) - parseFloat(bValue) : parseFloat(bValue) - parseFloat(aValue);
        }
        
        return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// ===== CHARTS AND VISUALIZATIONS =====

/**
 * Create simple bar chart
 */
function createBarChart(canvasId, data, labels) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const maxValue = Math.max(...data);
    const chartHeight = canvas.height - 40;
    const barWidth = canvas.width / data.length - 10;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    data.forEach((value, index) => {
        const barHeight = (value / maxValue) * chartHeight;
        const x = index * (barWidth + 10) + 5;
        const y = chartHeight - barHeight;
        
        // Draw bar
        ctx.fillStyle = '#667eea';
        ctx.fillRect(x, y, barWidth, barHeight);
        
        // Draw value
        ctx.fillStyle = '#333';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(value, x + barWidth / 2, y - 5);
        
        // Draw label
        ctx.fillText(labels[index], x + barWidth / 2, chartHeight + 15);
    });
}

/**
 * Create progress chart
 */
function createProgressChart(canvasId, current, target) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const percentage = (current / target) * 100;
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(centerX, centerY) - 20;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw background circle
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 20;
    ctx.stroke();
    
    // Draw progress arc
    const startAngle = -Math.PI / 2;
    const endAngle = startAngle + (2 * Math.PI * percentage / 100);
    
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.strokeStyle = '#667eea';
    ctx.lineWidth = 20;
    ctx.stroke();
    
    // Draw percentage text
    ctx.fillStyle = '#333';
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(Math.round(percentage) + '%', centerX, centerY);
}

// ===== NOTIFICATION SYSTEM =====

/**
 * Request notification permission
 */
function requestNotificationPermission() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showToast('Notifications enabled!', 'success');
            }
        });
    }
}

/**
 * Show browser notification
 */
function showNotification(title, body, icon = null) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: body,
            icon: icon || BASE_URL + 'assets/images/favicon.png'
        });
    }
}

// ===== LOCAL STORAGE =====

/**
 * Save data to localStorage
 */
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (error) {
        console.error('Failed to save to localStorage:', error);
        return false;
    }
}

/**
 * Get data from localStorage
 */
function getFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (error) {
        console.error('Failed to get from localStorage:', error);
        return null;
    }
}

/**
 * Remove data from localStorage
 */
function removeFromLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Failed to remove from localStorage:', error);
        return false;
    }
}

// ===== INITIALIZATION =====

/**
 * Initialize application
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize search filters
    const searchInputs = document.querySelectorAll('[data-filter]');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const tableId = this.dataset.filter;
            filterTable(tableId, this);
        });
    });
    
    // Initialize password strength indicators
    const passwordInputs = document.querySelectorAll('[data-strength]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            showPasswordStrength(this.value, this);
        });
    });
    
    // Request notification permission on first user interaction
    document.addEventListener('click', function requestOnce() {
        requestNotificationPermission();
        document.removeEventListener('click', requestOnce);
    }, { once: true });
    
    console.log('ActiveCore Gym initialized successfully');
});

// ===== EXPORT FUNCTIONS FOR GLOBAL USE =====
window.ActiveCoreGym = {
    formatDate,
    formatTime,
    sanitizeInput,
    showLoading,
    hideLoading,
    showToast,
    confirmAction,
    ajaxRequest,
    validateEmail,
    validatePassword,
    showPasswordStrength,
    isLoggedIn,
    getCurrentUser,
    logout,
    loadContent,
    filterTable,
    sortTable,
    createBarChart,
    createProgressChart,
    requestNotificationPermission,
    showNotification,
    saveToLocalStorage,
    getFromLocalStorage,
    removeFromLocalStorage
};
