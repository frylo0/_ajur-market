const importer = require('../env/webpack.importer');

const imported = importer([
  require.context('./Logic/', true, /\.js$/),
  require.context('./Attach/', true, /\./),
]);

import './Basic/devicer/devicer';
import './Blocks/header/header';
import './Basic/logo/logo';
import './Basic/button/button';
import './Blocks/search/search';
import './Basic/input/input';
import './Basic/footer/footer';
import './Blocks/menu/menu';
