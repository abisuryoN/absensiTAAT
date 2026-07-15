import './bootstrap'; // Laravel default bootstrap.js (axios setup, etc)
import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';

window.bootstrap = bootstrap;
window.Alpine = Alpine;

Alpine.start();
