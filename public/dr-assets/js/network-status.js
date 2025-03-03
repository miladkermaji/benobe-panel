document.addEventListener('DOMContentLoaded', () => {
 const modal = document.getElementById('network-modal');

 function checkNetworkStatus() {
  if (!navigator.onLine) {
   modal.classList.remove('hidden');
   modal.classList.add('active');
   document.body.style.overflow = 'hidden';
  } else {
   modal.classList.remove('active');
   setTimeout(() => {
    modal.classList.add('hidden');
   }, 300);
   document.body.style.overflow = 'auto';
  }
 }

 checkNetworkStatus();
 window.addEventListener('online', checkNetworkStatus);
 window.addEventListener('offline', checkNetworkStatus);
});