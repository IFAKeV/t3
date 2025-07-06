// IFAK Ticketsystem - Main JavaScript
// Version 2.0

document.addEventListener('DOMContentLoaded', function() {
    console.log('IFAK Ticketsystem v2.0 geladen');

    // Service Worker registrieren und Notification-Rechte anfragen
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/static/js/service-worker.js')
            .then(function(reg) {
                console.log('Service Worker registriert');
            })
            .catch(function(err) {
                console.error('Service Worker Registrierung fehlgeschlagen', err);
            });
    }
    if ('Notification' in window && Notification.permission !== 'granted') {
        Notification.requestPermission();
    }
    
    // Flash-Messages automatisch ausblenden
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
    
    // Ticket-Zeilen klickbar machen
    const ticketRows = document.querySelectorAll('.ticket-row');
    ticketRows.forEach(function(row) {
        row.style.cursor = 'pointer';
    });
    
    // Formulare: Required-Felder markieren
    const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    requiredInputs.forEach(function(input) {
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label && !label.textContent.includes('*')) {
            label.innerHTML = label.innerHTML.replace(':', '*:');
        }
    });
});

// Utility-Funktionen
function showLoading(element) {
    element.disabled = true;
    element.textContent = 'Lädt...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.textContent = originalText;
}

// Export für andere Module
window.IFAK = {
    showLoading: showLoading,
    hideLoading: hideLoading
};

// Lightbox für Bilder
$(document).ready(function() {
    // Lightbox-HTML einmalig erstellen
    $('body').append(`
        <div class="lightbox" id="lightbox">
            <span class="lightbox-close">&times;</span>
            <img class="lightbox-content" id="lightbox-img">
        </div>
    `);
    
    // Click-Event für Attachment-Bilder
    $(document).on('click', '.attachment-preview img', function(e) {
        e.preventDefault();
        $('#lightbox-img').attr('src', $(this).attr('src'));
        $('#lightbox').fadeIn(300);
    });
    
    // Lightbox schließen
    $('#lightbox, .lightbox-close').click(function() {
        $('#lightbox').fadeOut(300);
    });
    
    // ESC-Taste schließt Lightbox
    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            $('#lightbox').fadeOut(300);
        }
    });
});
