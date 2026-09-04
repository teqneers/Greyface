import React from 'react';

import {ListModule} from './ListModule';
import {autoWhitelist} from './lists';

const AutoWhitelistModule: React.FC = () => <ListModule build={autoWhitelist}/>;

export default AutoWhitelistModule;
