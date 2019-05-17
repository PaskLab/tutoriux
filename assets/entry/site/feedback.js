require('./app');

import Tutoriux from '../../scripts/global/tutoriux';

jQuery(document).ready(function() {
	Tutoriux.initForm();
	$('form').submit(function(){
		$(feedback_with_id).val(window.screen.width);
	});
});
