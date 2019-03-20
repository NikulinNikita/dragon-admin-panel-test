import DealerLogin from './components/pages/DealerLogin.vue';
import Baccarat from './components/pages/Baccarat.vue';
import Roulette from './components/pages/Roulette.vue';

export const routes = [
    {path: '/', name: 'DealerLogin', component: DealerLogin},
    {path: '/baccarat', name: 'BaccaratField', component: Baccarat, meta: { requiresAuth: true }},
    {path: '/roulette', name: 'RouletteField', component: Roulette, meta: { requiresAuth: true }},
];