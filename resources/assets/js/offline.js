window.network_status = function() {
  return navigator.onLine ? 'online' : 'offline';
};

window.addEventListener('online', function () {
  window.network_status = 'online';
});

window.addEventListener('offline', function () {
  window.network_status = 'offline';
});
