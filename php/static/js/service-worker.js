self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'IFAK Ticketsystem';
    const options = {
        body: data.body || '',
        icon: '../img/ifak-ticket-logo.svg'
    };
    const registration = self.registration;
    event.waitUntil(registration.showNotification(title, options));
});
