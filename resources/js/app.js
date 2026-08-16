import './bootstrap';
import Alpine from 'alpinejs';
import { Passkeys } from '@laravel/passkeys';

window.Passkeys = Passkeys;

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
