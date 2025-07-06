self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'IFAK Ticketsystem';
    const options = {
        body: data.body || '',
        icon: '/static/img/ifak-ticket-logo.svg'
    };
    event.waitUntil(self.registration.showNotification(title, options));
});
