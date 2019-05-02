// Global CSS and Scripts
require('../global/_app_global');

// Page CSS
require('../../styles/site/layout/layout.scss');
require('../../styles/site/layout/themes/tutoriux.scss');
require('../../styles/site/search.scss');

// Page Script
var Metronic = require('../../scripts/global/metronic');
var Layout = require('../../scripts/site/layout');
import Tutoriux from '../../scripts/global/tutoriux';
import TutoriuxSearch from '../../scripts/site/search_engine';

// Page Script
jQuery(document).ready(function() {
	Metronic.init();
	Layout.init();
	Tutoriux.init();
	Tutoriux.initFrontend();
	TutoriuxSearch.init(algolia_config);
});