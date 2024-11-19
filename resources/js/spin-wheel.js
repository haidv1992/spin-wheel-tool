import {createApp} from 'vue';
import SpinWheel from './components/SpinWheel.vue';

createApp(SpinWheel, {
    items: window.spinWheelData.items,
    config: window.spinWheelData.config,
    canSpin: window.spinWheelData.canSpin,
    csrfToken: window.spinWheelData.csrfToken,
}).mount('#spin-wheel-app');
