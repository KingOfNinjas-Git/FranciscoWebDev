// Home page specific functionality

// Typewriter effect
function initTypewriter() {
    const typewriterElement = document.getElementById('typewriter');
    if (!typewriterElement) return;

    const text = "Welcome to My Portfolio!";
    let index = 0;
    let isDeleting = false;

    function typeWriter() {
        if (!isDeleting) {
            typewriterElement.textContent = text.substring(0, index + 1);
            index++;
            if (index === text.length) {
                setTimeout(() => isDeleting = true, 2000);
            }
        } else {
            typewriterElement.textContent = text.substring(0, index - 1);
            index--;
            if (index === 0) {
                isDeleting = false;
            }
        }
        setTimeout(typeWriter, isDeleting ? 100 : 150);
    }

    // Start typewriter after a delay
    setTimeout(typeWriter, 500);
}

// Initialize home page functionality
document.addEventListener('DOMContentLoaded', function() {
    initTypewriter();
});