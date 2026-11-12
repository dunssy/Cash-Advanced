/**
 * PT Technodrives - Cash Advance System
 * Application Main Scripts
 */

// Initialize Lucide Icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Global UI Helpers
window.reinitIcons = function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};
