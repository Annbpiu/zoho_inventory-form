import './bootstrap';
import { createApp } from 'vue';
import SalesOrderForm from './components/SalesOrderForm.vue';

const app = createApp({
    components: { SalesOrderForm },
    template: '<SalesOrderForm />'
});

app.mount('#app');
