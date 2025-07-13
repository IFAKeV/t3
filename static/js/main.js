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
        row.addEventListener('click', function() {
            const href = row.getAttribute('data-href');
            if (href) {
                window.location = href;
            }
        });
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
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.createElement('div');
    lightbox.id = 'lightbox';
    lightbox.className = 'lightbox';

    const closeBtn = document.createElement('span');
    closeBtn.className = 'lightbox-close';
    closeBtn.textContent = '\u00d7';

    const img = document.createElement('img');
    img.id = 'lightbox-img';
    img.className = 'lightbox-content';

    lightbox.appendChild(closeBtn);
    lightbox.appendChild(img);
    document.body.appendChild(lightbox);

    document.addEventListener('click', function(e) {
        if (e.target.matches('.attachment-preview img')) {
            e.preventDefault();
            img.src = e.target.src;
            lightbox.style.display = 'block';
        } else if (e.target === lightbox || e.target === closeBtn) {
            lightbox.style.display = 'none';
        }
    });

    document.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            lightbox.style.display = 'none';
        }
    });
});
