self.addEventListener('install', event => {
  console.log('Service Worker installiert');
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  console.log('Service Worker aktiviert');
});

self.addEventListener('push', event => {
  let data = {};
  if (event.data) {
    data = event.data.json();
  }
  const title = data.title || 'IFAK Ticketsystem';
  const options = {
    body: data.body || 'Neue Benachrichtigung',
    icon: '/static/img/ifak-ticket-logo.svg'
  };
  event.waitUntil(self.registration.showNotification(title, options));
});
