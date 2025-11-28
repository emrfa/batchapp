import './bootstrap';

import Alpine from 'alpinejs';
import dashboard from './components/dashboard';
import receiving from './components/receiving';
import logs from './components/logs';
import reports from './components/reports';

window.Alpine = Alpine;

Alpine.data('dashboard', dashboard);
Alpine.data('receiving', receiving);
Alpine.data('logs', logs);
Alpine.data('reports', reports);

Alpine.start();
