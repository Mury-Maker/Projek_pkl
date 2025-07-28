// public/js/global-utils.js

// Variable for CSRF token, accessible globally
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Variable global for CKEditor instance (if applicable)
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
    let headers = new Headers(options.headers || {}); // Gunakan objek Headers untuk penanganan yang lebih baik

    // Logika penanganan Content-Type untuk FormData
    if (options.body instanceof FormData) {
        // Jika body adalah FormData, JANGAN SET Content-Type. Browser akan mengurusnya.
        // Hapus Content-Type yang mungkin sudah ada dari headers yang diberikan.
        headers.delete('Content-Type'); 
    } else if (!headers.has('Content-Type')) {
        // Jika body BUKAN FormData dan Content-Type belum diset, asumsikan JSON.
        headers.set('Content-Type', 'application/json');
    }

    // Selalu set Accept untuk API responses
    if (!headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }
    
    // Pastikan X-CSRF-TOKEN selalu ada
    if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken);
    }

    options.headers = headers; // Assign Headers object back to options

    try {
        console.log('fetchAPI: Memulai request ke', url, options); // Log untuk debugging
        const response = await fetch(url, options);
        console.log('fetchAPI: Menerima response. Status:', response.status); // Log status

        let data = null;
        const contentType = response.headers.get("content-type");

        // Coba parsing body hanya jika Content-Type adalah JSON
        if (contentType && contentType.includes("application/json")) {
            data = await response.json();
            console.log('fetchAPI: JSON berhasil diparsing. Data:', data); // Log data yang diparsing
        } else {
            console.warn('fetchAPI: Respons bukan JSON. Content-Type:', contentType);
            // Jika respons bukan JSON tapi status OK, bisa jadi itu success tanpa body (misal 204 No Content)
            // atau body kosong.
            if (response.ok) {
                return { success: true, message: "Operasi berhasil." }; 
            }
        }

        if (!response.ok) {
            // Jika respons TIDAK OK, lempar error. Gunakan data dari server jika ada.
            throw new Error(data?.message || data?.error || `HTTP Error! status: ${response.status} (${response.statusText || 'Unknown Status'})`);
        }
        
        return data; // Kembalikan data yang sudah diparsing untuk respons sukses

    } catch (error) {
        console.error('fetchAPI: Menangkap kesalahan umum:', error); // Log error yang ditangkap
        throw error; // Lempar kembali error agar bisa ditangkap oleh event listener submit
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