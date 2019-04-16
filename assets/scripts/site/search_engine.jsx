/**
 * Module name: TutoriuxSearch
 */
module.exports = function () {

	// PRIVATE

	var moment = require('../../plugins/moment/moment');

	/**
	 * @type {{config: {debug: boolean, app_id: null, api_key: null, index_prefix: string}, locale: string, initiated: boolean, translations: {server_error: {en: string, fr: string}, search_placeholder: {en: string, fr: string}, clear_all: {en: string, fr: string}, results_on: {en: string, fr: string}, one_result: {en: string, fr: string}, no_result_found: {en: string, fr: string}, pages: {en: string, fr: string}, page: {en: string, fr: string}, english: {en: string, fr: string}, french: {en: string, fr: string}, written_by: {en: string, fr: string}, translated_by: {en: string, fr: string}}}}
	 */
	var internal = {
		config: {
			debug: false,
			app_id: null,
			api_key: null,
			index_prefix: 'prod'
		},
		locale: (typeof session_locale !== 'undefined') ? session_locale : 'en',
		initiated: false,
		translations: {
			server_error: {en: '<h4 class="text-center">Sorry, an internal error occurred ...</h4>', fr: '<h4 class="text-center">Désolé, une erreur interne est survenue ...</h4>'},
			search_placeholder: {en: 'Search what you need ...', fr: 'Recherchez ce dont vous avez besoin ...'},
			clear_all: {en: 'Clear all', fr: 'Tout effacer'},
			results_on: {en: 'results on', fr: 'résultats sur'},
			one_result: {en: 'One result found', fr: 'Un résultat trouvé'},
			no_result_found: {en: 'No results for these criteria', fr: 'Aucun résultat pour ces critères'},
			pages: {en: 'pages', fr: 'pages'},
			page: {en: 'page', fr: 'page'},
			english: {en: 'English', fr: 'Anglais'},
			french: {en: 'French', fr: 'Français'},
			written_by: {en: 'Written by', fr: 'Écrit par'},
			translated_by: {en: 'Translated by', fr: 'Traduit par'}
		}
	};

	/**
	 * @param config
	 * @private
	 */
	var _initialization = function (config) {
		$.extend(internal.config, config);
		// Set locale for date format
		moment.locale(internal.locale);
	};

	/**
	 * Translation helper
	 * @param token
	 * @returns {*}
	 * @private
	 */
	var _trans = function (token) {
		return (internal.translations[token] != undefined) ? internal.translations[token][internal.locale] : token;
	};

	/**
	 * Display search view
	 * @private
	 */
	var _searchView = function () {

		$('.search-button').on('click tap', function () {
			if (internal.initiated) {
				$('#search_view').fadeIn();
				$('#alg-search-input').find('input').select();
			} else {
				_initInstantSearch();
			}
		});
		$('#close_search_view, .nav a').on('click tap', function () {
			$('#alg-search-input').find('input').blur();
			$('#search_view').fadeOut();
		});

		// Enable escape key
		$(document).on('keyup', function(e){
			if (e.keyCode == 27) {
				$('#search_view').fadeOut();
			}
		});
	};

	/**
	 * Handle filter collapse/expand
	 * @private
	 */
	var _filterHandle = function () {
		$('#search_view').on('click', '.filter-handle', function () {
			var $filterPortlet = $('#filter-portlet'),
				$toggleIcon = $filterPortlet.find('.filter-handle i'),
				$portletBody = $filterPortlet.find('.portlet-body');

			if ($toggleIcon.hasClass('fa-angle-double-down')) {
				$toggleIcon.removeClass('fa-angle-double-down');
				$toggleIcon.addClass('fa-angle-double-up');
				$portletBody.removeClass('hidden-xs');
				$portletBody.removeClass('hidden-sm');
			} else {
				$toggleIcon.removeClass('fa-angle-double-up');
				$toggleIcon.addClass('fa-angle-double-down');
				$portletBody.addClass('hidden-xs');
				$portletBody.addClass('hidden-sm');
			}
		});
	};

	/**
	 * Algolia Instant Search API
	 * @private
	 */
	var _initInstantSearch = function () {
		var client = instantsearch({
			appId: internal.config.app_id,
			apiKey: internal.config.api_key,
			indexName: internal.config.index_prefix + '_Document_' + internal.locale,
			urlSync: true
		});

		var widgets = [

			instantsearch.widgets.searchBox({
				container: '#alg-search-input',
				placeholder: _trans('search_placeholder'),
				autofocus: false,
				poweredBy: false,
				wrapInput: false,
				cssClasses: {
					input: 'form-control input-circle-right input-lg'
				}
			}),

			instantsearch.widgets.currentRefinedValues({
				container: '#alg-search-refinement',
				templates: {
					item: function (props) {
						return ( <RefinementItem { ...props } /> );
					},
					clearAll: function () {
						return ( <ClearAllBtn /> );
					}
				},
				cssClasses: {
					root: 'margin-top-10'
				}
			}),

			instantsearch.widgets.refinementList({
				container: '#alg-type-filter',
				attributeName: 'type',
				operator: 'or',
				templates: {
					item: function (props) {
						return ( <FilterItem { ...props } /> );
					}
				}
			}),

			instantsearch.widgets.hierarchicalMenu({
				container: '#alg-categories-filter',
				attributes: ['treeCategories.lvl0', 'treeCategories.lvl1', 'treeCategories.lvl2'],
				limit: 100,
				templates: {
					item: function (props) {
						return ( <FilterItem { ...props } /> );
					}
				}
			}),

			instantsearch.widgets.sortBySelector({
				container: '#alg-language',
				indices: [
					{name: internal.config.index_prefix + '_Document_en', label: _trans('english') },
					{name: internal.config.index_prefix + '_Document_fr', label: _trans('french') }
				],
				cssClasses: {
					root: 'form-control input-sm'
				}

			}),

			instantsearch.widgets.stats({
				container: '#alg-stats',
				templates: {
					body: function (props) {
						return ( <Stats { ...props } /> );
					}
				}
			}),

			instantsearch.widgets.hits({
				container: '#alg-hits',
				hitsPerPage: 10,
				templates: {
					item: function (props) {
						return ( <HitItem { ...props } /> );
					},
					empty: function (props) {
						return ( <NoResult { ...props } /> );
					}
				}
			}),

			instantsearch.widgets.pagination({
				container: '#alg-pagination',
				cssClasses: {
					root: 'pagination',
					first: 'first arrow',
					last: 'last arrow',
					previous: 'previous arrow',
					next: 'next arrow',
					item: 'number',
					active: 'selected number'
				}
			})
		];

		client.on('render', function () {
			$('.tooltips', '#search_view').tooltip();
		});

		client.once('render', function(){
			internal.initiated = true;
			$('#search_view').fadeIn();
			$('#alg-search-input').find('input').select();
		});

		widgets.forEach(client.addWidget, client);
		client.start();
	};

	/**
	 * Filter item
	 */
	var FilterItem = React.createClass({
		render: function () {
			var item = function () {
				if (this.props.isRefined) {
					return (
						<a href="javascript:;" className="btn btn-xs blue-madison">
							<i className="fa fa-dot-circle-o"></i>
							&nbsp;
							{ this.props.name }
						</a>
					);
				} else {
					return (
						<a href="javascript:;" className="btn btn-xs btn-link">
							<i className="fa fa-circle-thin"></i>
							{ this.props.name }
						</a>
					);
				}
			}.bind(this);
			return (
				<div>
					<span className="count pull-right">{ this.props.count }</span>
					{ item() }
				</div>
			);
		}
	});

	/**
	 * Refinement Item
	 */
	var RefinementItem = React.createClass({
		render: function () {
			return (
				<button className="btn btn-xs default">
					<i className="fa fa-times"></i>
					&nbsp;
					{ this.props.name }
				</button>
			);
		}
	});

	/**
	 * Clear all refinements button
	 */
	var ClearAllBtn = React.createClass({
		render: function () {
			return (
				<button className="btn btn-xs red">
					<i className="fa fa-trash-o"></i>
					&nbsp;
					{ _trans('clear_all') }
					&nbsp;
					<i className="fa fa-arrow-right"></i>
				</button>
			);
		}
	});

	/**
	 * Stats template for search statistics
	 */
	var Stats = React.createClass({
		render: function () {
			var displayStats = function () {
				if (this.props.hasManyResults) {
					return this.props.nbHits + ' ' + _trans('results_on') + ' ' + this.props.nbPages + ' ' + ((this.props.nbPages > 1) ? _trans('pages') : _trans('page'));
				} else if (this.props.hasOneResult) {
					return _trans('one_result');
				}
			}.bind(this);
			return (
				<em>
					«&nbsp;
					{ displayStats() }
					&nbsp;»
				</em>
			);
		}
	});

	/**
	 * Hit result item
	 */
	var HitItem = React.createClass({
		render: function () {
			var insertName = function () {
				return { __html: this.props._highlightResult.name.value };
			}.bind(this),
				insertDescription = function () {
					if (this.props._highlightResult.description != undefined) {
						return { __html: this.props._highlightResult.description.value };
					}
				}.bind(this),
				categories = this.props._highlightResult.categories.map(function (item, index) {
					return (
						<Category key={ index } value={ item.value } />
					);
				}),
				insertCategories = function () {
					if (this.props._highlightResult.categories.length) {
						return (
							<ul className="list-inline sub-categories">
								{ categories }
							</ul>
						);
					}
				}.bind(this),
				insertAuthors = function () {
					if (this.props.is_translation) {
						return (
							<li>
								<i className="fa fa-language tooltips" data-original-title={ _trans('translated_by') + ' ' + this.props.author } data-container="body"></i>
								&nbsp;
								{ this.props.author }
							</li>
						);
					}
				}.bind(this);
			return (
				<div>
					<h2><a href={ this.props.url } dangerouslySetInnerHTML={ insertName() }></a></h2>
					<ul className="list-inline">
						<li>
							<i className="fa fa-user tooltips" data-original-title={ _trans('written_by') + ' ' + this.props.original_author } data-container="body"></i>
							&nbsp;
							{ this.props.original_author }
						</li>
						{ insertAuthors() }
					</ul>
					<ul className="list-inline">
						<li>
							<i className="fa fa-calendar"></i>
							&nbsp;
							<span className="font-grey-gallery">
								{ moment.unix(this.props.date).fromNow() }
							</span>
						</li>
						<li>
							<i className="fa fa-heart"></i>
							&nbsp;
							<span className="font-grey-gallery">
								{ this.props.votes }
							</span>
						</li>
						<li>
							<i className="fa fa-eye"></i>
							&nbsp;
							<span className="font-grey-gallery">
								{ this.props.page_views }
							</span>
						</li>
					</ul>
					<p dangerouslySetInnerHTML={ insertDescription() } />
					{ insertCategories() }
				</div>
			);
		}
	});

	/**
	 * Category item
	 */
	var Category = React.createClass({
		render: function () {
			var insertName = function () {
					return { __html: this.props.value };
				}.bind(this);
			return (
				<li>
					<span className="sub-category" dangerouslySetInnerHTML={ insertName() } ></span>
				</li>
			);
		}
	});

	/**
	 * No Result template
	 */
	var NoResult = React.createClass({
		render: function () {
			return (
				<div className="text-center margin-top-20">
					<h3>{ _trans('no_result_found') }</h3>
					<h4><em>« { this.props.query } »</em></h4>
				</div>
			);
		}
	});

	// PUBLIC
	return {

		init: function (config) {
			_initialization(config);
			_searchView();
			_filterHandle();
		}
	};

}();