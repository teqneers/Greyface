import React from 'react';

import {ListModule} from './ListModule';
import {whitelist} from './lists';

const WhitelistModule: React.FC = () => <ListModule build={whitelist}/>;

export default WhitelistModule;
