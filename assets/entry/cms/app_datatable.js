require('./app');

// Page CSS
require('../../plugins/select2/css/select2.css');
require('../../plugins/select2/css/select2-bootstrap.min.css');
require('../../plugins/datatables/datatables.css');

// Page scripts
require('../../plugins/select2/js/select2.full');
require('../../plugins/datatables/datatables');
require('../../plugins/jquery.tablednd_0_5');
import Tutoriux from '../../scripts/global/tutoriux';

// Set Tutoriux as global so it can be accessed on a by-page basis for configuration purpose.
window.Tutoriux = Tutoriux;

/**
 *  Datatable can be ignited with the following helper:
 *
 *  jQuery(document).ready(function() {
 *		$(function(){
 *			Tutoriux.initDataTable({
 *				"sortIgnore": [4],
 *				"searchIgnore": [2,4],
 *				"stripHtmlTags": [0]
 *			});
 *		});
 *	});
 */

