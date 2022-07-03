/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Compile new Javascript file
import './javascript/method1.js';

// start the Stimulus application
import './bootstrap';

import '../node_modules/bootstrap/js/dist/modal';

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;
