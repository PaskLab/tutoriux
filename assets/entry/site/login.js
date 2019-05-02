// Global CSS and Scripts
require('../global/_app_global');

// Page CSS
require('../../styles/site/layout/layout.scss');
require('../../styles/site/layout/themes/tutoriux.scss');
require('../../styles/site/pages/login3.scss');

// Page Script
var Metronic = require('../../scripts/global/metronic');
var Layout = require('../../scripts/site/layout');
require('../../plugins/jquery-validation/js/jquery.validate');
import Tutoriux from '../../scripts/global/tutoriux';
import Login from '../../scripts/site/pages/login';

// Page Script
jQuery(document).ready(function() {
	Metronic.init();
	Layout.init();
	Login.init();
	Tutoriux.initForm();
});