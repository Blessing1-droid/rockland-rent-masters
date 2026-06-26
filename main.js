/**
 * main.js - Core JavaScript for Rockland Rent Masters
 * Modern ES6+ with all interactive features
 */

// ============================================
// DOM Ready Event Handler
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initMobileNavigation();
    initScrollEffects();
    initRevealAnimations();
    initNotificationDropdown();
    initThemeToggle();
    initSavedProperties();
    initRecentlyViewed();
    initCompareTool();
    initAppointmentBooking();
    initPropertyInquiry();
    initPasswordStrength();
    initFormValidation();
    initToastNotifications();
    initLazyLoading();
});

// ============================================
// Mobile Navigation
// ============================================
function initMobileNavigation() {
    const toggleBtn = document.getElementById('navToggle');
    const navMenu = document.getElementById('navLinks');
    
    if (toggleBtn && navMenu) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navMenu.classList.contains('active') && 
                !navMenu.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
    }
}

// ============================================
// Scroll Effects (Navbar shrink, back to top)
// ============================================
function initScrollEffects() {
    const navbar = document.querySelector('.navbar');
    const backToTop = createBackToTopButton();
    
    window.addEventListener('scroll', () => {
        // Navbar shrink effect
        if (window.scrollY > 50) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }
        
        // Back to top button
        if (window.scrollY > 300) {
            backToTop?.classList.add('visible');
        } else {
            backToTop?.classList.remove('visible');
        }
    });
    
    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

function createBackToTopButton() {
    // Check if button already exists
    if (document.querySelector('.back-to-top')) {
        return document.querySelector('.back-to-top');
    }
    
    const btn = document.createElement('button');
    btn.innerHTML = '↑';
    btn.className = 'back-to-top';
    btn.setAttribute('aria-label', 'Back to top');
    Object.assign(btn.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        width: '45px',
        height: '45px',
        borderRadius: '50%',
        background: 'var(--primary, #059669)',
        color: 'white',
        border: 'none',
        cursor: 'pointer',
        opacity: '0',
        visibility: 'hidden',
        transition: 'all 0.3s ease',
        zIndex: '999',
        fontSize: '20px'
    });
    document.body.appendChild(btn);
    return btn;
}

// ============================================
// Scroll Reveal Animations
// ============================================
function initRevealAnimations() {
    const revealElements = document.querySelectorAll('.reveal');
    
    if (revealElements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    
    revealElements.forEach(el => observer.observe(el));
}

// ============================================
// Notification Dropdown
// ============================================
function initNotificationDropdown() {
    const notificationBtn = document.querySelector('.notification-btn');
    const dropdown = document.querySelector('.notification-menu');
    
    if (notificationBtn && dropdown) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('active');
            markNotificationsAsRead();
        });
        
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    }
}

async function markNotificationsAsRead() {
    try {
        const response = await fetch('/api/notifications/mark-read.php', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const badge = document.querySelector('.notification-badge');
            if (badge) badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error marking notifications:', error);
    }
}

// ============================================
// Theme Toggle (Dark/Light Mode)
// ============================================
function initThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const savedTheme = localStorage.getItem('theme') || 
                       (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    themeToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
    
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        showToast(`${newTheme === 'dark' ? 'Dark' : 'Light'} mode activated`, 'info');
    });
}

// ============================================
// Saved Properties (Favorites)
// ============================================
function initSavedProperties() {
    const saveButtons = document.querySelectorAll('.save-property');
    
    saveButtons.forEach(btn => {
        // Remove existing event listeners to prevent duplicates
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const propertyId = newBtn.dataset.id;
            
            if (!propertyId) {
                console.error('No property ID found');
                return;
            }
            
            if (!isLoggedIn()) {
                showToast('Please login to save properties', 'warning');
                setTimeout(() => {
                    window.location.href = '/pages/auth/login.php';
                }, 1500);
                return;
            }
            
            const isSaved = newBtn.classList.contains('active');
            
            try {
                const response = await fetch('/api/save-property.php', {
                    method: isSaved ? 'DELETE' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ property_id: propertyId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    newBtn.classList.toggle('active');
                    newBtn.innerHTML = newBtn.classList.contains('active') ? '❤️' : '🤍';
                    showToast(data.message, 'success');
                    
                    // Update saved count
                    updateSavedCount(data.action === 'added' ? 1 : -1);
                } else {
                    showToast(data.message || 'Error saving property', 'error');
                }
            } catch (error) {
                console.error('Save property error:', error);
                showToast('Error saving property', 'error');
            }
        });
    });
}

function updateSavedCount(delta) {
    const savedCount = document.querySelector('.saved-count');
    if (savedCount) {
        let count = parseInt(savedCount.textContent) || 0;
        count = Math.max(0, count + delta);
        savedCount.textContent = count;
    }
}

// ============================================
// Recently Viewed Properties
// ============================================
function initRecentlyViewed() {
    // Track when viewing a property detail page
    const propertyElement = document.querySelector('[data-property-id]');
    if (propertyElement) {
        const propertyId = propertyElement.dataset.propertyId;
        if (propertyId) {
            addToRecentlyViewed(propertyId);
        }
    }
    
    // Display recently viewed from localStorage
    displayRecentlyViewed();
}

function addToRecentlyViewed(propertyId) {
    let recent = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    recent = recent.filter(id => id !== propertyId);
    recent.unshift(propertyId);
    recent = recent.slice(0, 10);
    localStorage.setItem('recentlyViewed', JSON.stringify(recent));
}

function displayRecentlyViewed() {
    const container = document.getElementById('recently-viewed');
    if (!container) return;
    
    const recentIds = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    if (recentIds.length === 0) {
        container.innerHTML = '<p class="text-muted">No recently viewed properties</p>';
        return;
    }
    
    fetch('/api/get-properties.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: recentIds })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.properties) {
            container.innerHTML = data.properties.map(property => `
                <div class="recent-property-item">
                    <img src="${property.main_image || '/assets/images/placeholder.jpg'}" alt="${escapeHtml(property.title)}">
                    <div class="recent-property-info">
                        <a href="/pages/renter/property.php?id=${property.id}">${escapeHtml(property.title)}</a>
                        <span>$${property.price}/mo</span>
                    </div>
                </div>
            `).join('');
        }
    })
    .catch(error => console.error('Error loading recent properties:', error));
}

// ============================================
// Compare Properties Tool
// ============================================
function initCompareTool() {
    const compareButtons = document.querySelectorAll('.compare-property');
    let compareList = getCompareList();
    updateCompareUI();
    
    compareButtons.forEach(btn => {
        // Remove existing listeners
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const propertyId = newBtn.dataset.id;
            
            if (!propertyId) return;
            
            if (compareList.includes(propertyId)) {
                compareList = compareList.filter(id => id !== propertyId);
                showToast('Removed from comparison', 'info');
            } else if (compareList.length < 3) {
                compareList.push(propertyId);
                showToast('Added to comparison', 'success');
            } else {
                showToast('You can compare up to 3 properties at a time', 'warning');
                return;
            }
            
            localStorage.setItem('compareList', JSON.stringify(compareList));
            updateCompareUI();
            
            const compareNowBtn = document.querySelector('.compare-now-btn');
            if (compareNowBtn) {
                compareNowBtn.style.display = compareList.length >= 2 ? 'flex' : 'none';
            }
        });
    });
    
    // Compare now button
    const compareNowBtn = document.getElementById('compareNow');
    if (compareNowBtn) {
        compareNowBtn.addEventListener('click', () => {
            const currentList = getCompareList();
            if (currentList.length >= 2) {
                window.location.href = `/pages/renter/compare.php?ids=${currentList.join(',')}`;
            }
        });
    }
}

function getCompareList() {
    return JSON.parse(localStorage.getItem('compareList') || '[]');
}

function updateCompareUI() {
    const compareList = getCompareList();
    const compareCount = document.querySelector('.compare-count');
    if (compareCount) compareCount.textContent = compareList.length;
    
    document.querySelectorAll('.compare-property').forEach(btn => {
        if (compareList.includes(btn.dataset.id)) {
            btn.classList.add('active');
            btn.textContent = 'Remove from Compare';
        } else {
            btn.classList.remove('active');
            btn.textContent = 'Compare';
        }
    });
}

// ============================================
// Appointment Booking
// ============================================
function initAppointmentBooking() {
    const bookingForm = document.getElementById('booking-form');
    if (!bookingForm) return;
    
    // Initialize date picker
    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        const minDate = new Date().toISOString().split('T')[0];
        dateInput.min = minDate;
    }
    
    // Time slot selection
    const timeSlots = document.querySelectorAll('.time-slot');
    if (timeSlots.length > 0) {
        timeSlots.forEach(slot => {
            slot.addEventListener('click', () => {
                timeSlots.forEach(s => s.classList.remove('selected'));
                slot.classList.add('selected');
                const selectedTimeInput = document.getElementById('selected-time');
                if (selectedTimeInput) {
                    selectedTimeInput.value = slot.dataset.time;
                }
            });
        });
    }
    
    // Form submission
    bookingForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = bookingForm.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Booking...';
        
        const formData = new FormData(bookingForm);
        
        try {
            const response = await fetch('/api/book-appointment.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showToast('Appointment booked successfully!', 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showToast(data.message || 'Booking failed', 'error');
            }
        } catch (error) {
            console.error('Booking error:', error);
            showToast('Error booking appointment', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// ============================================
// Property Inquiry System
// ============================================
function initPropertyInquiry() {
    const inquiryForm = document.getElementById('inquiry-form');
    if (!inquiryForm) return;
    
    inquiryForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = inquiryForm.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        
        const formData = new FormData(inquiryForm);
        
        try {
            const response = await fetch('/api/send-inquiry.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showToast('Inquiry sent successfully!', 'success');
                inquiryForm.reset();
            } else {
                showToast(data.message || 'Failed to send', 'error');
            }
        } catch (error) {
            console.error('Inquiry error:', error);
            showToast('Error sending inquiry', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// ============================================
// Password Strength Meter
// ============================================
function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;
    
    // Check if strength bar already exists
    if (!document.querySelector('.password-strength-bar')) {
        const strengthBar = document.createElement('div');
        strengthBar.className = 'password-strength-bar';
        strengthBar.style.cssText = `
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        `;
        passwordInput.parentNode.appendChild(strengthBar);
    }
    
    passwordInput.addEventListener('input', () => {
        const strength = checkPasswordStrength(passwordInput.value);
        updateStrengthBar(strength);
    });
}

function checkPasswordStrength(password) {
    if (!password) return 0;
    
    let score = 0;
    if (password.length >= 8) score++;
    if (password.match(/[a-z]/)) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^a-zA-Z0-9]/)) score++;
    return score;
}

function updateStrengthBar(strength) {
    const bar = document.querySelector('.password-strength-bar');
    if (!bar) return;
    
    const colors = ['#ef4444', '#ef4444', '#f59e0b', '#10b981', '#10b981'];
    const widths = ['20%', '40%', '60%', '80%', '100%'];
    const texts = ['', 'Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    
    const index = Math.max(0, Math.min(4, strength - 1));
    bar.style.width = widths[index] || '0%';
    bar.style.background = colors[index] || '#ef4444';
    bar.setAttribute('data-text', texts[strength] || '');
}

// ============================================
// Form Validation
// ============================================
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => clearFieldError(field));
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    clearFieldError(field);
    
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    if (field.type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Enter a valid email address';
    }
    
    if (field.type === 'password' && field.id === 'confirm_password') {
        const passwordField = field.form.querySelector('#password');
        if (passwordField && value !== passwordField.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');
    field.style.borderColor = '#ef4444';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = `
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
    `;
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) existingError.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ============================================
// Toast Notifications
// ============================================
function initToastNotifications() {
    // Style for toast container
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        Object.assign(container.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            zIndex: '1000',
            display: 'flex',
            flexDirection: 'column',
            gap: '10px'
        });
        document.body.appendChild(container);
    }
}

function showToast(message, type = 'info', duration = 3000) {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = { success: '✓', error: '✗', warning: '⚠', info: 'ℹ' };
    
    toast.innerHTML = `
        <span class="toast-icon" style="font-size: 18px;">${icons[type] || icons.info}</span>
        <span class="toast-message" style="flex: 1;">${escapeHtml(message)}</span>
        <button class="toast-close" style="background: none; border: none; font-size: 18px; cursor: pointer; color: inherit;">×</button>
    `;
    
    Object.assign(toast.style, {
        background: 'var(--bg-primary, #ffffff)',
        color: 'var(--text-primary, #111827)',
        padding: '12px 16px',
        borderRadius: '8px',
        boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        minWidth: '250px',
        maxWidth: '350px',
        animation: 'slideInRight 0.3s ease',
        borderLeft: `4px solid ${getTypeColor(type)}`
    });
    
    container.appendChild(toast);
    
    const closeBtn = toast.querySelector('.toast-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => toast.remove());
    }
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }
    }, duration);
}

function getTypeColor(type) {
    const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    return colors[type] || colors.info;
}

// Add animation styles
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(styleSheet);

// ============================================
// Lazy Loading Images
// ============================================
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if (images.length === 0) return;
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.dataset.src;
                if (src) {
                    img.src = src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px'
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// ============================================
// Helper Functions
// ============================================
function isLoggedIn() {
    return document.body.hasAttribute('data-logged-in') && 
           document.body.getAttribute('data-logged-in') === 'true';
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Export for use in other modules
window.showToast = showToast;
window.isLoggedIn = isLoggedIn;
window.escapeHtml = escapeHtml;
/**
 * charts.js - Chart.js Integration for Analytics Dashboard
 * PRD v1.1: Property analytics, occupancy rates, and trends
 */

// Chart instances storage
const chartInstances = {};

// ============================================
// Initialize All Charts
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initPropertyViewsChart();
    initOccupancyChart();
    initPriceTrendsChart();
    initApplicationStatusChart();
});

// ============================================
// Property Views Chart (Last 30 Days)
// ============================================
function initPropertyViewsChart() {
    const canvas = document.getElementById('property-views-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Sample data - replace with API call
    const viewsData = {
        labels: getLast30Days(),
        datasets: [{
            label: 'Property Views',
            data: generateRandomData(30, 50, 200),
            borderColor: '#c62828',
            backgroundColor: 'rgba(198, 40, 40, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    };
    
    chartInstances.views = new Chart(ctx, {
        type: 'line',
        data: viewsData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Views'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
}

// ============================================
// Occupancy Rate Chart
// ============================================
function initOccupancyChart() {
    const canvas = document.getElementById('occupancy-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Fetch occupancy data from API
    fetchOccupancyData().then(data => {
        chartInstances.occupancy = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Vacant', 'Maintenance'],
                datasets: [{
                    data: [data.occupied, data.vacant, data.maintenance],
                    backgroundColor: ['#2e7d32', '#c62828', '#ed6c02'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} units (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
}

async function fetchOccupancyData() {
    try {
        const response = await fetch('/rockland-rent-masters/api/occupancy-stats.php');
        if (response.ok) {
            return await response.json();
        }
    } catch (error) {
        console.error('Error fetching occupancy data:', error);
    }
    
    // Return sample data if API not ready
    return { occupied: 42, vacant: 8, maintenance: 3 };
}

// ============================================
// Price Trends Chart
// ============================================
function initPriceTrendsChart() {
    const canvas = document.getElementById('price-trends-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    chartInstances.priceTrends = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: '1 Bedroom',
                    data: [1200, 1225, 1250, 1280, 1300, 1320, 1350, 1370, 1400, 1420, 1450, 1480],
                    backgroundColor: '#c62828',
                    borderRadius: 4
                },
                {
                    label: '2 Bedroom',
                    data: [1600, 1630, 1650, 1680, 1700, 1730, 1760, 1790, 1820, 1850, 1880, 1920],
                    backgroundColor: '#ef5350',
                    borderRadius: 4
                },
                {
                    label: '3 Bedroom',
                    data: [2100, 2130, 2150, 2180, 2210, 2240, 2280, 2320, 2360, 2400, 2450, 2500],
                    backgroundColor: '#ff8f00',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: $${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Average Monthly Rent ($)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        }
    });
}

// ============================================
// Application Status Chart
// ============================================
function initApplicationStatusChart() {
    const canvas = document.getElementById('application-status-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    fetchApplicationStats().then(data => {
        chartInstances.applications = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Reviewed', 'Approved', 'Rejected', 'Withdrawn'],
                datasets: [{
                    label: 'Applications',
                    data: [data.pending, data.reviewed, data.approved, data.rejected, data.withdrawn],
                    backgroundColor: [
                        '#ed6c02',
                        '#0288d1',
                        '#2e7d32',
                        '#c62828',
                        '#757575'
                    ],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} applications`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Applications'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
}

async function fetchApplicationStats() {
    try {
        const response = await fetch('/rockland-rent-masters/api/application-stats.php');
        if (response.ok) {
            return await response.json();
        }
    } catch (error) {
        console.error('Error fetching application stats:', error);
    }
    
    // Return sample data
    return { pending: 12, reviewed: 8, approved: 15, rejected: 5, withdrawn: 3 };
}

// ============================================
// Update Chart Data (Real-time)
// ============================================
async function updatePropertyViewsChart(propertyId, days = 30) {
    try {
        const response = await fetch(`/rockland-rent-masters/api/property-views.php?property_id=${propertyId}&days=${days}`);
        const data = await response.json();
        
        if (chartInstances.views && data.views) {
            chartInstances.views.data.datasets[0].data = data.views;
            chartInstances.views.update();
        }
    } catch (error) {
        console.error('Error updating chart:', error);
    }
}

// ============================================
// Helper Functions
// ============================================
function getLast30Days() {
    const dates = [];
    const today = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        dates.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    }
    
    return dates;
}

function generateRandomData(count, min, max) {
    return Array.from({ length: count }, () => Math.floor(Math.random() * (max - min + 1) + min));
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updatePropertyViewsChart,
        calculateAffordability
    };
}
/**
 * dashboard.js - Analytics and Charts for Dashboards
 * Chart.js integration for property analytics
 */

let chartInstances = {};

document.addEventListener('DOMContentLoaded', function() {
    initPropertyViewsChart();
    initApplicationStatusChart();
    initOccupancyChart();
    initRevenueChart();
    initPriceTrendsChart();
    initVisitorChart();
});

function initPropertyViewsChart() {
    const canvas = document.getElementById('viewsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.views = new Chart(ctx, {
        type: 'line',
        data: {
            labels: getLast30Days(),
            datasets: [{
                label: 'Property Views',
                data: generateRandomData(30, 500, 5000),
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.05)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#059669',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6
            }, {
                label: 'Applications',
                data: generateRandomData(30, 10, 200),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6 } },
                tooltip: {
                    backgroundColor: 'var(--bg-primary)',
                    titleColor: 'var(--text-primary)',
                    bodyColor: 'var(--text-secondary)',
                    borderColor: 'var(--border-light)',
                    borderWidth: 1,
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${context.raw.toLocaleString()}`
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'var(--border-light)' }, ticks: { callback: v => v.toLocaleString() } },
                x: { grid: { display: false } }
            }
        }
    });
}

function initApplicationStatusChart() {
    const canvas = document.getElementById('applicationStatusChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.applications = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Reviewed', 'Approved', 'Rejected'],
            datasets: [{
                data: [12, 8, 15, 5],
                backgroundColor: ['#f59e0b', '#3b82f6', '#059669', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true } },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

function initOccupancyChart() {
    const canvas = document.getElementById('occupancyChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.occupancy = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Occupied Units',
                    data: generateRandomData(12, 80, 95),
                    backgroundColor: '#059669',
                    borderRadius: 8,
                    barPercentage: 0.7
                },
                {
                    label: 'Vacant Units',
                    data: generateRandomData(12, 5, 20),
                    backgroundColor: '#f59e0b',
                    borderRadius: 8,
                    barPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Percentage (%)' } },
                x: { grid: { display: false } }
            }
        }
    });
}

function initRevenueChart() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.revenue = new Chart(ctx, {
        type: 'line',
        data: {
            labels: getLast12Months(),
            datasets: [{
                label: 'Monthly Revenue ($)',
                data: generateRandomData(12, 5000, 25000),
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#059669',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: { callbacks: { label: (ctx) => `$${ctx.raw.toLocaleString()}` } }
            },
            scales: {
                y: { title: { display: true, text: 'Revenue ($)' }, ticks: { callback: v => '$' + v.toLocaleString() } }
            }
        }
    });
}

function initPriceTrendsChart() {
    const canvas = document.getElementById('priceTrendsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.priceTrends = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: getLast12Months(),
            datasets: [
                { label: '1 Bedroom', data: generateRandomData(12, 1200, 1800), backgroundColor: '#059669', borderRadius: 6 },
                { label: '2 Bedroom', data: generateRandomData(12, 1600, 2400), backgroundColor: '#3b82f6', borderRadius: 6 },
                { label: '3 Bedroom', data: generateRandomData(12, 2100, 3200), backgroundColor: '#f59e0b', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { tooltip: { callbacks: { label: (ctx) => `$${ctx.raw.toLocaleString()}` } } },
            scales: { y: { title: { display: true, text: 'Average Rent ($)' }, ticks: { callback: v => '$' + v.toLocaleString() } } }
        }
    });
}

function initVisitorChart() {
    const canvas = document.getElementById('visitorChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    chartInstances.visitors = new Chart(ctx, {
        type: 'line',
        data: {
            labels: getLast30Days(),
            datasets: [
                { label: 'Unique Visitors', data: generateRandomData(30, 200, 1500), borderColor: '#059669', borderWidth: 2, fill: false, tension: 0.4 },
                { label: 'Page Views', data: generateRandomData(30, 500, 5000), borderColor: '#3b82f6', borderWidth: 2, fill: false, tension: 0.4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// Helper Functions
function getLast30Days() {
    const dates = [];
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        dates.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    }
    return dates;
}

function getLast12Months() {
    const months = [];
    for (let i = 11; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        months.push(date.toLocaleDateString('en-US', { month: 'short' }));
    }
    return months;
}

function generateRandomData(count, min, max) {
    return Array.from({ length: count }, () => Math.floor(Math.random() * (max - min + 1) + min));
}

// Update charts with real data
async function updateDashboardStats() {
    try {
        const response = await fetch('/api/dashboard-stats.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalProperties')?.textContent = data.totalProperties;
            document.getElementById('totalApplications')?.textContent = data.totalApplications;
            document.getElementById('totalRevenue')?.textContent = '$' + data.totalRevenue.toLocaleString();
            document.getElementById('occupancyRate')?.textContent = data.occupancyRate + '%';
        }
    } catch (error) {
        console.error('Error updating dashboard stats:', error);
    }
}

// Export for use
window.updateDashboardStats = updateDashboardStats;
/**
 * map.js - Leaflet Map Integration for Property Locations
 */

let mainMap = null;
let markers = [];
let currentBounds = null;

// Initialize map when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('property-map');
    if (mapElement) {
        initPropertyMap('property-map');
        loadNearbyProperties();
    }
});

function initPropertyMap(elementId, centerLat = 41.8781, centerLng = -87.6298, zoom = 12) {
    const element = document.getElementById(elementId);
    if (!element) return null;
    
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet not loaded');
        return null;
    }
    
    mainMap = L.map(elementId).setView([centerLat, centerLng], zoom);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
        minZoom: 3
    }).addTo(mainMap);
    
    return mainMap;
}

async function loadNearbyProperties() {
    if (!mainMap) return;
    
    try {
        const response = await fetch('/api/nearby-properties.php');
        const data = await response.json();
        
        if (data.success && data.properties) {
            addPropertyMarkers(data.properties);
        }
    } catch (error) {
        console.error('Error loading nearby properties:', error);
    }
}

function addPropertyMarkers(properties, onMarkerClick = null) {
    if (!mainMap) return;
    
    clearMarkers();
    const bounds = [];
    
    properties.forEach(property => {
        if (!property.latitude || !property.longitude) return;
        
        const lat = parseFloat(property.latitude);
        const lng = parseFloat(property.longitude);
        
        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: var(--primary);
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                cursor: pointer;
                transition: transform 0.2s;
            " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                $${Math.round(property.price / 1000)}k
            </div>`,
            iconSize: [30, 30],
            popupAnchor: [0, -15]
        });
        
        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(mainMap);
        
        const popupContent = `
            <div style="min-width: 240px; max-width: 280px;">
                <img src="${property.main_image || '/assets/images/placeholder.jpg'}" 
                     style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                <h4 style="margin: 0 0 5px 0; font-size: 16px;">${escapeHtml(property.title)}</h4>
                <p style="margin: 0 0 5px 0; font-size: 13px; color: #666;">📍 ${escapeHtml(property.address)}</p>
                <p style="margin: 0 0 5px 0; font-weight: bold; color: var(--primary);">$${formatNumber(property.price)}/month</p>
                <div style="display: flex; gap: 8px; margin-top: 10px;">
                    <span>🛏️ ${property.bedrooms}</span>
                    <span>🛁 ${property.bathrooms}</span>
                    <span>📐 ${property.sqft} sqft</span>
                </div>
                <a href="/pages/renter/property.php?id=${property.id}" 
                   style="display: block; text-align: center; margin-top: 10px; padding: 8px; background: var(--primary); color: white; text-decoration: none; border-radius: 6px; font-size: 13px;">
                    View Details →
                </a>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        if (onMarkerClick) {
            marker.on('click', () => onMarkerClick(property));
        }
        
        markers.push(marker);
        bounds.push([lat, lng]);
    });
    
    if (bounds.length > 0 && mainMap) {
        const boundsGroup = L.latLngBounds(bounds);
        mainMap.fitBounds(boundsGroup, { padding: [50, 50] });
    }
}

function clearMarkers() {
    markers.forEach(marker => {
        if (mainMap) mainMap.removeLayer(marker);
    });
    markers = [];
}

function getUserLocation() {
    if (!navigator.geolocation) {
        showToast('Geolocation not supported', 'error');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            if (mainMap) {
                mainMap.setView([latitude, longitude], 14);
                L.marker([latitude, longitude])
                    .bindPopup('📍 You are here')
                    .addTo(mainMap)
                    .openPopup();
            }
        },
        (error) => {
            console.error('Geolocation error:', error);
            showToast('Unable to get your location', 'warning');
        }
    );
}

async function searchLocationByAddress(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`);
        const data = await response.json();
        
        if (data && data[0]) {
            const { lat, lon } = data[0];
            if (mainMap) {
                mainMap.setView([parseFloat(lat), parseFloat(lon)], 14);
                L.marker([parseFloat(lat), parseFloat(lon)])
                    .bindPopup(`📍 ${address}`)
                    .addTo(mainMap)
                    .openPopup();
            }
            return { lat: parseFloat(lat), lng: parseFloat(lon) };
        } else {
            showToast('Location not found', 'error');
            return null;
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        showToast('Error searching location', 'error');
        return null;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function showToast(message, type) {
    if (window.showToast) {
        window.showToast(message, type);
    } else {
        console.log(`[${type}] ${message}`);
    }
}

// Export functions for global use
window.initPropertyMap = initPropertyMap;
window.addPropertyMarkers = addPropertyMarkers;
window.getUserLocation = getUserLocation;
window.searchLocationByAddress = searchLocationByAddress;