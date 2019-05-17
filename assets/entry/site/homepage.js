require('./app');

// Page CSS
require('../../styles/site/home.scss');

// Page scripts
require('../../plugins/jquery-match-height/dist/jquery.matchHeight');
import TutoriuxHome from '../../scripts/site/home';

jQuery(document).ready(function() {
	TutoriuxHome.init();
});