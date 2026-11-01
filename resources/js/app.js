import './bootstrap';
import Alpine from 'alpinejs';
import { Passkeys } from '@laravel/passkeys';

window.Passkeys = Passkeys;

// Do not start standalone Alpine on Filament panels as Filament provides and manages its own extended Alpine instance with form plugins
const isFilamentPanel = /^\/(manage|instructor|student|parent)(\/|$)/.test(window.location.pathname);
if (!isFilamentPanel && !window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
