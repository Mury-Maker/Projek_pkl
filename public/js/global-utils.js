// public/js/global-utils.js

// Variable for CSRF token, accessible globally
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Variable global for CKEditor instance (if applicable)
// currentEditorInstance hanya akan digunakan di use-case-logic.js
let currentEditorInstance; 

/**
 * Displays a toast notification.
 * @param {string} message - The message to display.
 * @param {'success' | 'error' | 'loading'} type - The type of notification.
 * @param {number} duration - How long the notification should stay (in ms). 0 for no auto-hide.
 * @returns {{notifId: string, showTimeoutId: number | null, element: HTMLElement}} - Info about the created notification.
 */
function showNotification(message, type = 'success', duration = 3000) {
    const container = document.getElementById('notification-container');
    if (!container) {
        console.error('Notification container not found!');
        return;
    }

    const notifId = 'notif-' + Date.now();
    const notifDiv = document.createElement('div');
    notifDiv.id = notifId;
    notifDiv.className = `notification-message ${type}`;

    let iconClass = '';
    let timeoutDuration = duration;
    let delayBeforeShow = 0;

    if (type === 'success') {
        iconClass = 'fa-solid fa-check-circle text-green';
        timeoutDuration = 3000; // Default 3 seconds
    } else if (type === 'error') {
        iconClass = 'fa-solid fa-times-circle text-red-700';
    } else if (type === 'loading') {
        iconClass = 'fa-solid fa-spinner fa-spin text-blue-700';
        timeoutDuration = 0; // Loading notification won't auto-hide
        delayBeforeShow = 500; // Add 0.5 sec delay for loading notification
    }

    notifDiv.innerHTML = `<i class="notification-icon ${iconClass}"></i> ${message}`;
    
    const showTimeoutId = setTimeout(() => {
        container.appendChild(notifDiv);
        setTimeout(() => notifDiv.classList.add('show'), 10);
    }, delayBeforeShow);

    if (timeoutDuration > 0) {
        setTimeout(() => {
            notifDiv.classList.remove('show');
            setTimeout(() => notifDiv.remove(), 500);
        }, timeoutDuration + delayBeforeShow);
    }

    return { notifId: notifId, showTimeoutId: showTimeoutId, element: notifDiv };
}

/**
 * Hides a notification.
 * @param {string | {notifId: string, showTimeoutId: number | null, element: HTMLElement}} notifInfo - The ID of the notification or the object returned by showNotification.
 */
function hideNotification(notifInfo) {
    let notifElement;
    let showTimeoutId;

    if (typeof notifInfo === 'string') {
        notifElement = document.getElementById(notifInfo);
    } else if (typeof notifInfo === 'object' && notifInfo !== null) {
        notifElement = notifInfo.element;
        showTimeoutId = notifInfo.showTimeoutId;
    }

    if (showTimeoutId) {
        clearTimeout(showTimeoutId);
    }
    
    if (notifElement && notifElement.parentNode) {
        notifElement.classList.remove('show');
        setTimeout(() => notifElement.remove(), 500);
    }
}

/**
 * Performs a Fetch API request.
 * @param {string} url - The URL to fetch.
 * @param {RequestInit} options - Fetch API options.
 * @returns {Promise<any>} - A promise that resolves to the JSON response.
 */
async function fetchAPI(url, options = {}) {
    let defaultHeaders = {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
    };

    // If body is NOT FormData, assume JSON and set Content-Type header
    if (!(options.body instanceof FormData)) {
        defaultHeaders['Content-Type'] = 'application/json';
    }

    options.headers = { ...defaultHeaders, ...options.headers };

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `HTTP Error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Fetch API Error:', error);
        throw error;
    }
}


/**
 * Displays a central success popup.
 * @param {string} message - The message to display.
 */
function showCentralSuccessPopup(message) {
    const centralSuccessPopup = document.getElementById('central-success-popup');
    const centralPopupMessage = document.getElementById('central-popup-message');

    if (centralPopupMessage) {
        centralPopupMessage.textContent = message;
    }
    if (centralSuccessPopup) {
        centralSuccessPopup.classList.add('show');
        setTimeout(() => {
            centralSuccessPopup.classList.remove('show');
        }, 1000); // Popup bertahan 1 detik
    }
}

/**
 * Updates the category button text (e.g., "ePesantren" from "epesantren").
 * @param {string} categoryKey - The slug of the category.
 * @param {HTMLElement} targetTextElement - The DOM element to update.
 */
function updateCategoryButtonText(categoryKey, targetTextElement) {
    let categoryDisplayName = categoryKey.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    if (targetTextElement) {
        targetTextElement.textContent = categoryDisplayName;
    }
}