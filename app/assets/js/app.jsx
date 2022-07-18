import 'react-hot-loader';
import React from 'react';
import ReactDOM from 'react-dom';
import ReactModal from 'react-modal';

import Application from './application/Application';

import 'bootstrap/dist/css/bootstrap.min.css';

const root = document.getElementById('app');
if (root !== null) {
    const {...config} = {...JSON.parse(root.dataset.config)};

    ReactModal.setAppElement(root);

    ReactDOM.render(<Application {...config}/>, root);

}
