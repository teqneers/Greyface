import React from 'react';
import {createRoot} from 'react-dom/client';
import ReactModal from 'react-modal';

import Application from './application/Application';
import {initSettings} from './application/settings';
import {initI18n} from './application/i18n';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'react-datepicker/dist/react-datepicker.css';
import '../css/app.scss';

const root = document.getElementById('app');
if (root !== null) {
    const {...config} = {...JSON.parse(root.dataset.config)};
    initSettings();
    ReactModal.setAppElement(root);

    initI18n().then(() => {
        createRoot(root).render(<Application {...config}/>);
    });
}
