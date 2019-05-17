/**
 * Module name: TutoriuxHome
 */
export default (function () {

	// PRIVATE

	/**
	 * @type {{config: {debug: boolean}, locale: string, translations: {}}}
	 */
	var internal = {
		config: {
			debug: false
		},
		locale: typeof session_locale !== 'undefined' ? session_locale : 'en',
		translations: { }
	};

	/**
	 * @param config
	 * @private
	 */
	var _initialization = function (config) {
		$.extend(internal.config, config);
	};

	/**
	 * Translation helper
	 * @param token
	 * @returns {*}
	 * @private
	 */
	var _trans = function (token) {
		return internal.translations[token] != undefined ? internal.translations[token][internal.locale] : token;
	};

	/**
	 * @private
	 */
	var _PortletMatchHeight = function () {
		if ($.fn.matchHeight) {
			$('.portlet-body').matchHeight();
		} else if (internal.config.debug) {
			console.error('Missing "jquery-match-height" javascript library');
		}
	};

	// PUBLIC
	return {

		init: function (config) {
			_initialization(config);
			_PortletMatchHeight();
		}
	};
}());