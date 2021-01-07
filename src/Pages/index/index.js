import './../../bundle';
import md5 from 'md5';

// Code libs and plugins
import { globalEventone } from '../../Plugins/eventone.js';
import { VisibilityController } from '../../Plugins/visibility.js';
import { Queue } from '../../Plugins/queue.js';

window.VisibilityController = VisibilityController;
window.Queue = Queue;
globalEventone(false);

import './../../Blocks/card-product/card-product';
import './../../Blocks/card-shop/card-shop';
import './../../Blocks/how-work-block/how-work-block';
