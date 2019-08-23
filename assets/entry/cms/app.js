// Global CSS and Scripts
require('../global/_app_global');

// Page CSS
require('../../styles/cms/layout/layout.scss');
require('../../styles/cms/layout/themes/default.scss');

// Page Script
var Metronic = require('../../scripts/global/metronic');
var Layout = require('../../scripts/cms/layout');
import Tutoriux from '../../scripts/global/tutoriux';

jQuery(document).ready(function() {
	Metronic.init();
	Layout.init();
	Tutoriux.init();
});