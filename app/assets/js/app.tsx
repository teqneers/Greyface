import {createRoot} from 'react-dom/client';

import Application from './application/Application';
import {initSettings} from './application/settings';
import {initI18n} from './application/i18n';
import type {ApplicationConfigProps as ApplicationConfig} from './application/ApplicationContext';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'react-datepicker/dist/react-datepicker.css';
import '../css/app.scss';

const root = document.getElementById('app');
if (root !== null && root.dataset.config) {
    const config = JSON.parse(root.dataset.config) as ApplicationConfig;
    initSettings();

    initI18n().then(() => {
        createRoot(root).render(<Application {...config}/>);
    });
}
