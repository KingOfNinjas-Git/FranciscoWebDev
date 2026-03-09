// About page specific functionality

// Read More Toggle Logic
function initReadMore() {
    const btn = document.getElementById('read-more-btn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        document.getElementById('extended-bio').classList.remove('hidden');
        this.style.display = 'none';
    });
}

// Initialize about page functionality
document.addEventListener('DOMContentLoaded', function() {
    initReadMore();
});