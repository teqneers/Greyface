import React from 'react';

import {ListModule} from './ListModule';
import {blacklist} from './lists';

const BlacklistModule: React.FC = () => <ListModule build={blacklist}/>;

export default BlacklistModule;
