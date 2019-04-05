/*jshint esversion: 6 */
let Tutoriux = function () {
	'use strict';

	// PRIVATE

	/**
	 * @type {{locale: string, config: {headerSensitivity: number}, errorModal: internal.errorModal, infoModal: internal.infoModal, deleteModal: internal.deleteModal, confirmModal: internal.confirmModal, translations}}
	 */
	let internal = {
		locale: (typeof session_locale !== 'undefined') ? session_locale : 'en',
		config: {
			headerSensitivity: 10
		},
		errorModal: function () { return $('#error_modal'); },
		infoModal: function () { return $('#info_modal'); },
		deleteModal: function () { return $('#delete_modal'); },
		confirmModal: function () { return $('#confirm_modal'); },
		translations: TutoriuxTranslations
	};

	/**
	 * Translation helper
	 * @param token
	 * @returns {*}
	 * @private
	 */
	let _trans = function (token) {
		return (internal.translations[token] !== undefined) ? internal.translations[token][internal.locale] : token;
	};

	/**
	 * Bind Locale switcher
	 *
	 * @private
	 */
	let _bindLocaleSwitcher = function () {
		$('.locale', $('#edit-locales')).on('touchstart click', function(e) {
			e.preventDefault();
			window.location.href = window.location.href.replace(/[?#].*|$/, '?edit-locale=' + $(this).data('locale'));
		});
	};

	/**
	 * Bind DataTable order switch
	 *
	 * @private
	 */
	let _bindTableOrderSwitch = function () {
		if (jQuery().DataTable) {

			// LIST ORDER MODE
			$('a.list-order-mode').click(function(e){
				if ($(this).hasClass('selected')) {
					_cancelOrderMode($(this));
				} else {
					_activateOrderMode($(this));
				}
				e.preventDefault();
			});
		}
	};

	/**
	 * Bind DataTable order alpha btn
	 *
	 * @private
	 */
	let _bindTableOrderAlphaBtn = function () {

		let linkObject = $('a.list-order-alpha');

		if (jQuery().DataTable && linkObject.length) {

			// LIST ORDER MODE
			linkObject.click(function(e){
				let targetTable = $('#' + linkObject.data('table-id')),
					dtApi = targetTable.dataTable().api(),
					rows = targetTable.find('tbody tr'),
					elements = '';

				dtApi.order([dtApi.column('.name').index(), 'asc']).search('').page.len( -1 ).draw();
				$('#' + linkObject.data('table-id') + '_length select').select2('val', -1);

				for (let i = 0; i < rows.length; i++) {

					elements += $(rows[i]).attr('id') + ';';

					dtApi.cells('#'+rows[i].id, '.ordering').iterator('cell', function (context, row, column) {
						dtApi.cell(row, column).data(0);
					});
				}

				Metronic.blockUI({
					target: '#' + targetTable.attr('id'),
					boxed: true,
					message: TutoriuxTranslations.reordering[session_locale]
				});

				$.ajax({
					type: 'POST',
					url: linkObject.data('sort-url'),
					data: {
						elements: elements
					},
					success: function() {
						Metronic.unblockUI('#' + targetTable.attr('id'));
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						Metronic.unblockUI('#' + targetTable.attr('id'));

						let $errorModal = internal.errorModal();
						$errorModal.find('.modal-body').html(_trans('server_error'));
						$errorModal.modal();
					},
					complete: function () {
						dtApi.page.len(10).draw();
						$('#' + linkObject.data('table-id') + '_length select').select2('val', 10);
					}
				});
				e.preventDefault();
			});
		}
	};

	/**
	 * Bind DataTable
	 *
	 * Provide option wrapper to help future plugin update
	 * DataTable Version: 1.10.2 http://datatables.net/
	 *
	 * @param options
	 * @private
	 */
	let _bindDataTable = function (options) {

		if (!jQuery().dataTable) {
			return;
		}

		// Change dataTables error mode to throw instead of alert
		$.fn.dataTableExt.sErrMode = 'throw';

		let defaultOptions = {
			'selector': '.table',
			'locale': 'en',
			'searchable': true,
			'orderable': true,
			'initLength': 10,
			'displayLengths': [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, _trans('all')] // change per page values here
			],
			'sortIgnore': [],
			'searchIgnore': [],
			'stripHtmlTags': [],
			'hideColumns': [],
			'extraColumnDefs': [], // multi-column order example: [{ 'orderData': [ 0, 1 ], 'targets': 0 }, ...]
			'standardOptions': {} // Add standard DataTable options
		};

		$.extend(defaultOptions, options);

		let translations = {
			en: {
				'emptyTable': 'No data available in table',
				'infoEmpty': 'No matching entry',
				'zeroRecords': 'No matching records found',
				'info': 'Showing _START_ to _END_ of _TOTAL_ data(s)',
				'infoFiltered': '(filtered from _MAX_ total data(s))',
				'lengthMenu': '_MENU_ per page',
				'search': '',
				'processing': TutoriuxTranslations.processing['en'],
				'paginate': {
					'previous': TutoriuxTranslations.previous['en'],
					'next': TutoriuxTranslations.next['en']
				}
			},
			fr: {
				'emptyTable': 'Aucune donnée disponible',
				'infoEmpty': 'Aucune entrée correspondante',
				'zeroRecords': 'Aucun enregistrement correspondant',
				'info': 'Affichage de _START_ à _END_ sur _TOTAL_ donnée(s)',
				'infoFiltered': '(Résultats filtrés parmi _MAX_ donnée(s) totale(s))',
				'lengthMenu': '_MENU_ par page',
				'search': '',
				'processing': TutoriuxTranslations.processing['fr'],
				'paginate': {
					'previous': TutoriuxTranslations.previous['fr'],
					'next': TutoriuxTranslations.next['fr']
				}
			}
		};

		$(defaultOptions.selector).dataTable($.extend({
			'searching': defaultOptions.searchable,
			'ordering': defaultOptions.orderable,
			'lengthMenu': defaultOptions.displayLengths,
			'pageLength': defaultOptions.initLength,
			'order': [], // Initialise without sort
			'columnDefs': defaultOptions.extraColumnDefs.concat([
				{ 'orderable': false, 'targets': defaultOptions.sortIgnore },
				{ 'searchable': false, 'targets': defaultOptions.searchIgnore },
				{ 'visible': false, 'targets': defaultOptions.hideColumns },
				{ 'targets': defaultOptions.stripHtmlTags,
					'render': function ( data, type, full ) {
						if (type === 'filter') {
							return data.replace(/(<([^>]+)>)/ig,'');
						}

						return data;
					}
				}
			]),
			'language': translations[defaultOptions.locale],
			'drawCallback': function(){
				let linkObject = $('a.list-order-mode[data-table-id="' + $(this).attr('id') + '"]');

				if (linkObject.hasClass('selected')) {
					_cancelOrderMode(linkObject);
				}
			},
			'initComplete': function(settings, json) {

				// Different behavior if data computed on server side
				if (settings.oFeatures.bServerSide) {
					let dtApi = $('#'+$(this).attr('id')).dataTable().api(),
						$filterGroup = $('#'+$(this).attr('id')+'_filter'),
						$searchInput = $filterGroup.find('input'),
						$searchBtn = $filterGroup.find('.input-group-addon');

					$searchInput.unbind().bind('keyup', function(e) {
						if(e.keyCode == 13) {
							dtApi.search(this.value).draw();
						}
					});
					$searchBtn.unbind().bind('click touchstart', function() {
						dtApi.search($searchInput.val()).draw();
					});
					$searchBtn.css('cursor', 'pointer');
				}
			}
		},
		// override options
		defaultOptions.standardOptions
		));

		// Styles
		let dtSearchInput = $('.dataTables_filter input');
		let dtElemPerPage = $('.dataTables_length select');
		dtSearchInput.removeClass('input-small');
		dtSearchInput.wrap('<div class="input-group input-large"></div>');
		$('<span class="input-group-addon input-sm"><i class="fa fa-search"></i></span>').insertAfter('.dataTables_filter input');
		dtSearchInput.addClass('form-control'); // modify table search input
		dtElemPerPage.addClass('form-control input-xsmall input-inline'); // modify table per page dropdown
		dtElemPerPage.select2({minimumResultsForSearch: -1}); // initialize select2 dropdown
	};

	/**
	 * Activate Order Mode
	 *
	 * @param linkObject
	 * @private
	 */
	let _activateOrderMode = function (linkObject) {

		let targetTable = $('#' + linkObject.data('table-id')),
			dtApi = targetTable.dataTable().api();

		dtApi.order([dtApi.column('.ordering').index(), 'asc']).search('').page.len( -1 ).draw();
		$('#' + linkObject.data('table-id') + '_length select').select2('val', -1);

		// Add selected after all ReDraw
		linkObject.addClass('selected');
		linkObject.find('.fa-sort').toggleClass('fa-arrows-v').toggleClass('fa-sort');

		// Enable tableDnD plugin
		_listeSortable(targetTable, dtApi, linkObject);
	};

	/**
	 * Cancel order mode
	 *
	 * @param linkObject
	 * @private
	 */
	let _cancelOrderMode = function (linkObject) {

		linkObject.removeClass('selected');
		linkObject.find('.fa-arrows-v').toggleClass('fa-sort').toggleClass('fa-arrows-v');

		let targetTable = $('#' + linkObject.data('table-id')),
			dtApi = targetTable.dataTable().api();

		dtApi.page.len(10).draw();
		$('#' + linkObject.data('table-id') + '_length select').select2('val', 10);

		// Disable tableDnD plugin
		targetTable.find('tr').unbind();
		targetTable.find('tr').css('cursor', '');
	};

	/**
	 * Apply jquery.tableDnD on DataTable
	 *
	 * @param targetTable Table element from DOM
	 * @param dtApi JqueryDataTable Object
	 * @param linkObject
	 * @private
	 */
	let _listeSortable = function (targetTable, dtApi, linkObject) {

		targetTable.tableDnD({
			onDragClass: 'on_drag',
			onDrop: function(table, row) {
				let rows = table.tBodies[0].rows,
					elements = '';
				for (let i = 0; i < rows.length; i++) {

					elements += rows[i].id + ';';

					dtApi.cells('#'+rows[i].id, '.ordering').iterator('cell', function (context, row, column) {
						dtApi.cell(row, column).data(i+1);
					});
				}

				Metronic.blockUI({
					target: '#' + targetTable.attr('id'),
					boxed: true,
					message: TutoriuxTranslations.reordering[session_locale]
				});

				$.ajax({
					type: 'POST',
					url: linkObject.data('sort-url'),
					data: {
						elements: elements
					},
					success: function() {
						Metronic.unblockUI('#' + targetTable.attr('id'));
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						Metronic.unblockUI('#' + targetTable.attr('id'));

						let $errorModal = internal.errorModal();
						$errorModal.find('.modal-body').html(_trans('server_error'));
						$errorModal.modal();
					}
				});
			}
		});
	};

	/**
	 * Bind delete confirmation for entities
	 *
	 * @private
	 */
	let _bindDeleteConfirmation = function () {
		let $deleteModal = internal.deleteModal(),
			$deleteConfirm = $deleteModal.find('#delete_confirm');

		// Unbind confirm action on modal hide event
		$deleteModal.on('hide.bs.modal' ,function(e){
			$deleteConfirm.unbind();
		});

		$(document).on('click', 'a[data-delete-check]', function(e){

			let linkObj = $(this);

			Metronic.blockUI({
				target: '#main',
				boxed: true,
				message: TutoriuxTranslations.processing[session_locale]
			});

			$.ajax({
				type: 'GET',
				cache: false,
				url: linkObj.data('delete-check'),
				dataType: 'json',
				success: function (response){
					Metronic.unblockUI('#main');

					if (response.status === 'deletable') {

						$deleteConfirm.click(function(){
							window.location = linkObj.attr('href');
						});

						$deleteModal.find('.modal-body').html(response.template);

						$deleteModal.modal();

					} else {

						let $infoModal = internal.infoModal();

						$infoModal.find('.modal-body').html(response.template);

						$infoModal.modal();
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					Metronic.unblockUI('#main');

					let $errorModal = internal.errorModal();
					$errorModal.find('.modal-body').html(_trans('server_error'));
					$errorModal.modal();
				}
			});

			e.preventDefault();

		});
	};

	/**
	 * @private
	 */
	let _bindConfirmModal = function () {
		let $confirmModal = internal.confirmModal(),
			$confirmConfirm = $confirmModal.find('#confirm_confirm');

		// Unbind confirm action on modal hide event
		$confirmModal.on('hide.bs.modal' ,function () {
			$confirmConfirm.unbind();
		});
	};

	/**
	 * Handle Jquery Pulsate
	 *
	 * @param obj
	 * @param params
	 * @private
	 */
	let _pulsate = function (obj, params) {
		if (!jQuery().pulsate) {
			return;
		}

		if (Metronic.isIE8() == true) {
			return; // pulsate plugin does not support IE8 and below
		}

		obj.pulsate($.extend({
			color: '#2a6496',
			repeat: false
		}, params));
	};

	/**
	 * Handle treeChoice
	 *
	 * @private
	 */
	let _treeChoice = function () {
		if (typeof toggleSelectAllCheckbox !== 'function') {

			let select_all_objects = $('input.select_all');

			let translations = {
				en: {
					select_all: 'Select All',
					unselect_all: 'Unselect All'
				},
				fr: {
					select_all: 'Tout sélectionner',
					unselect_all: 'Désélectionner tous'
				}
			};

			let toggleSelectAllCheckbox = function (select_all, initialize) {
				if (!initialize || (initialize && !select_all.data('initialized'))) {
					select_all.data('initialized', 1);

					let childrens = select_all.parents('ul:first').find('input:checkbox').not('.select_all');

					if (childrens.length === childrens.filter(':checked').length) {
						if (!select_all.is(':checked')) {
							select_all.trigger('click');
							$('label[for="' + select_all.attr('id') + '"]').html(translations[session_locale].unselect_all);
						}
					} else {
						if (select_all.is(':checked')) {
							select_all.trigger('click');
							$('label[for="' + select_all.attr('id') + '"]').html(translations[session_locale].select_all);
						}
					}
				}
			};

			let toggleChildrens = function (checkbox) {
				if (checkbox.is(':checked')) {
					let level = checkbox.data('level');
					checkbox.closest('li').nextAll('li').each(function(){
						let children = $(this).find('input:checkbox');
						if (children.data('level') > level && !children.is(':checked')) {
							children.trigger('click');
						} else {
							return false;
						}
					});
				}
			};

			select_all_objects.each(function(i){
				toggleSelectAllCheckbox($(this), true);
			});

			select_all_objects.change(function(){
				let select_all = $(this);
				let childrens = select_all.parents('ul:first').find('input:checkbox').not('.select_all');

				if (select_all.is(':checked')) {
					childrens.each(function(){
						if (!$(this).is(':checked')) {
							$(this).trigger('click');
						}
					});
					$('label[for="' + select_all.attr('id') + '"]').html(translations[session_locale].unselect_all);
				} else {
					childrens.each(function(){
						if ($(this).is(':checked')) {
							$(this).trigger('click');
						}
					});
					$('label[for="' + select_all.attr('id') + '"]').html(translations[session_locale].select_all);
				}
			});

			select_all_objects.parents('ul').find('input:checkbox').not('.select_all').change(function(){
				toggleSelectAllCheckbox($(this).siblings('input.select_all'));
				toggleChildrens($(this));
			});
		}
	};

	let _bindHoneyPot = function () {
		$('form').submit(function(){
			$(this).find('input.spec_date').val(null);
		});
	};

	let _bindNotification = function () {
		let $headerNotification = $('#header-notification-bar'),
			toastrDisplayed = false;

		toastr.options = {
			'closeButton': true,
			'debug': false,
			'positionClass': 'toast-top-right',
			'onclick': null,
			'showDuration': '1000',
			'hideDuration': '1000',
			'timeOut': '5000',
			'extendedTimeOut': '1000',
			'showEasing': 'swing',
			'hideEasing': 'linear',
			'showMethod': 'fadeIn',
			'hideMethod': 'fadeOut'
		};

		$.each($headerNotification.find('.details'), function(index, value){
			if ($(value).data('toast') == true) {
				if ($(value).data('toast-url') !== undefined) {
					toastr.options.onclick = function () {
						document.location.href = $(value).data('toast-url');
					};
				} else {
					toastr.options.onclick = null;
				}
				toastr[$(value).data('toast-type')]($(value).text());
				toastrDisplayed = true;
			}
		});

		if (toastrDisplayed) {
			$.ajax({
				url: Routing.generate(session_locale + '__RG__system_frontend_notification_toastr'),
				method: 'PUT'
			});
		}

		$headerNotification.on('tap click', '.dropdown-toggle', function (){
			let $dropdownLink = $(this);
			if ($dropdownLink.find('.badge').length) {
				$.ajax({
					url: Routing.generate(session_locale + '__RG__system_frontend_notification_viewed'),
					method: 'PUT',
					dataType: 'json',
					success: function (data) {
						$dropdownLink.find('.badge').hide();
					},
					error: function (data) {
						let infoModal = $('#info_modal');

						let message = '<p class="lead text-center">' + TutoriuxTranslations.server_error[session_locale] + '</p>';

						infoModal.find('.modal-body').html($(message));

						infoModal.modal();
					}
				});
			}
		});
	};

	let _bindSummernote = function (options) {
		if (!jQuery().summernote) {
			return;
		}

		let language = {
			en: 'en-US',
			fr: 'fr-FR'
		};

		$('.summernote').summernote($.extend({
			height: 300,
			lang: language[session_locale],
			toolbar: [
				['oops', ['undo', 'redo']],
				['text-type', ['style']],
				['color', ['color']],
				['style', ['bold', 'italic', 'underline', 'strikethrough']],
				['erase', ['clear']],
				['s-script', ['superscript', 'subscript']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link', 'hr', 'table']],
				['misc', ['fullscreen', 'help']]
			],
			foreColor: 'red'
		}, options));

		// fix empty value on submit
		$('form').submit(function (e) {
			$.each($(this).find('.summernote'), function (i,el) {
				let content = $($(el).code());
				if (content.length == 1 && content.children().length == 1 && !content.text()) {
					$(el).val('');
				}
			});
		});
	};

	let _showBadges = function () {
		if (system_badges.unreadMessages > 0) {
			$('#hmenu-unread-messages').text(system_badges.unreadMessages).removeClass('hide');
		}
	};

	let _hideHeaderOnScroll = function () {
		// Hide Header on on scroll down
		let didScroll;
		let lastScrollTop = 0;
		let delta = internal.config.headerSensitivity;
		let $navbar = $('.page-header-menu');
		let navbarHeight = $navbar.outerHeight();

		$(window).scroll(function(event){
			didScroll = true;
		});

		setInterval(function() {
			if (didScroll) {
				hasScrolled();
				didScroll = false;
			}
		}, 250);

		function hasScrolled() {
			let st = $(this).scrollTop();

			// Make sure they scroll more than delta
			if(Math.abs(lastScrollTop - st) <= delta) {
				return;
			}

			// If they scrolled down and are past the navbar, add class .nav-up.
			// This is necessary so you never see what is "behind" the navbar.
			if (st > lastScrollTop && st > navbarHeight && st > 150){
				// Scroll Down
				if ($navbar.hasClass('fixed')) {
					$navbar.removeClass('nav-down').addClass('nav-up');
				}
			} else {
				// Scroll Up
				if(st + $(window).height() < $(document).height()) {
					$navbar.removeClass('nav-up').addClass('nav-down');
				}
			}

			lastScrollTop = st;
		}
	};
	
	// PUBLIC

	return {

		init: function () {
			_bindLocaleSwitcher();
			_bindDeleteConfirmation();
			_bindConfirmModal();
			_hideHeaderOnScroll();
		},
		setOptions: function (options) {
			$.extend(internal.config, options);
		},
		initDataTable: function (options) {
			_bindDataTable(options);
			_bindTableOrderSwitch();
			_bindTableOrderAlphaBtn();
		},
		treeChoice: function () {
			return _treeChoice();
		},
		initForm: function () {
			_bindHoneyPot();
		},
		initFrontend: function () {
			_bindNotification();
			_showBadges();
		},
		initSummernote: function (options, honeypot) {
			_bindSummernote(options);
			if (honeypot) {
				_bindHoneyPot();
			}
		},
		trans: function (token) {
			return _trans(token);
		}
	};

}();