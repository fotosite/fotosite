import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// iOS :active-Fix: Safari feuert :active auf Touch-Geräten nur,
// wenn ein touchstart-Listener auf document registriert ist.
document.addEventListener('touchstart', function () {}, { passive: true });
