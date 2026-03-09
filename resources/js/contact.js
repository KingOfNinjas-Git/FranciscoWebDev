// Contact page specific functionality

// Typewriter effect for hero
function initContactTypewriter() {
    const contactTypewriterElement = document.getElementById('contact-typewriter');
    if (!contactTypewriterElement) return;

    const contactText = "Let's Connect!";
    let contactIndex = 0;
    let contactIsDeleting = false;

    function contactTypeWriter() {
        if (!contactIsDeleting) {
            contactTypewriterElement.textContent = contactText.substring(0, contactIndex + 1);
            contactIndex++;
            if (contactIndex === contactText.length) {
                setTimeout(() => contactIsDeleting = true, 2000);
            }
        } else {
            contactTypewriterElement.textContent = contactText.substring(0, contactIndex - 1);
            contactIndex--;
            if (contactIndex === 0) {
                contactIsDeleting = false;
            }
        }
        setTimeout(contactTypeWriter, contactIsDeleting ? 100 : 150);
    }

    // Start typewriter after a delay
    setTimeout(contactTypeWriter, 500);
}

// Initialize contact page functionality
document.addEventListener('DOMContentLoaded', function() {
    initContactTypewriter();
});