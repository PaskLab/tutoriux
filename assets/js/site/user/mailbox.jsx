var TutoriuxMailbox = function() {

	// PRIVATE

	var internal = {
		locale: (typeof session_locale !== 'undefined') ? session_locale : 'en',
		url: Routing.generate(((typeof session_locale !== 'undefined') ? session_locale : 'en') + '__RG__section_id_35_messages', {'_format': 'json'}),
		generateUrl: function (route, parameters) {
			return Routing.generate(((typeof session_locale !== 'undefined') ? session_locale : 'en') + '__RG__' + route, parameters)
		},
		translations: {
			inbox: {en: 'Inbox', fr: 'Boîte de réception'},
			sent: {en: 'Sent', fr: 'Envoyés'},
			trash: {en: 'Trash', fr: 'Corbeille'},
			processing: {en: 'Processing ...', fr: 'Traitement ...'},
			search: {en: 'Search ...', fr: 'Rechercher ...'},
			of: {en: 'of', fr: 'de'},
			by: {en: 'By', fr: 'Par'},
			sent_on: {en: 'sent on', fr: 'envoyé le'},
			message: {en: 'message', fr: 'message'},
			no_object: {en: 'no object', fr: 'aucun objet'},
			mark_as_read: {en: 'Mark as Read', fr: 'Marquer comme lu'},
			delete: {en: 'Delete', fr: 'Supprimer'},
			recover: {en: 'Recover', fr: 'Récupérer'},
			hard_delete: {en: 'Delete for Good', fr: 'Supprimer'},
			empty_trash: {en: 'Empty Trash', fr: 'Vider la corbeille'},
			view_message: {en: 'Message', fr: 'Message'},
			close: {en: 'Close', fr: 'Fermer'},
			reply: {en: 'Reply', fr: 'Répondre'}
		}
	};

	var _trans = function (token) {
		return internal.translations[token][internal.locale];
	};

	var _bindCheckAll = function () {
		// handle group checkbox:
		jQuery('#mailbox-content').on('change', '.mail-group-checkbox', function () {
			var $set = jQuery('.mail-checkbox');
			var $checked = jQuery(this).is(":checked");
			jQuery($set).each(function () {
				$(this).attr("checked", $checked);
			});
			jQuery.uniform.update($set);
		}).on('change', '.mail-checkbox', function (event) {
			if (false == $(event.target).is(":checked")) {
				var $checkAll = jQuery('.mail-group-checkbox');
				$checkAll.attr("checked", false);
				jQuery.uniform.update($checkAll);
			}
		});
	};

	var MailBox = React.createClass({
		getInitialState: function() {
			return {
				data: [],
				title: _trans('inbox'),
				view: 'inbox',
				page: 1,
				total: 0,
				filter: null,
				id: null
			};
		},
		componentDidMount: function () {
			this.loadMessages(this.state.view, this.state.page, this.state.filter);
		},
		refresh: function (callback) {
			this.loadMessages(this.state.view, this.state.page, this.state.filter, callback)
		},
		changeView: function (view) {
			this.loadMessages(view, 1, this.state.filter);
		},
		filterMessage: function (filter) {
			this.loadMessages(this.state.view, this.state.page, filter);
		},
		loadMessages: function (view, page, filter, callback) {
			Metronic.blockUI({
				target: '#mailbox-content',
				boxed: true,
				message: _trans('processing')
			});
			$.ajax({
				url: internal.url,
				data: {
					action: 'list',
					view: view,
					length: this.props.dataPerPage,
					page: page,
					filter: filter
				},
				dataType: 'json',
				cache: false,
				success: function(data) {
					this.setState({
						data: data.data,
						view: data.view,
						title: _trans(data.view),
						page: data.page,
						total: data.total,
						filter: data.filter,
						id: null
					});

					if ('function' === typeof callback) {
						callback();
					}

					Metronic.unblockUI('#mailbox-content');
				}.bind(this),
				error: function(xhr, status, err) {
					console.error(internal.url, status, err.toString());
					Metronic.unblockUI('#mailbox-content');
				}.bind(this)
			});
		},
		readMessage: function (id) {
			Metronic.blockUI({
				target: '#mailbox-content',
				boxed: true,
				message: _trans('processing')
			});
			$.ajax({
				url: internal.url,
				data: {
					action: 'read',
					id: id
				},
				dataType: 'json',
				cache: false,
				success: function(data) {
					this.setState({
						data: data,
						id: id,
						title: _trans('view_message')
					});

					Metronic.unblockUI('#mailbox-content');
				}.bind(this),
				error: function(xhr, status, err) {
					console.error(internal.url, status, err.toString());
					Metronic.unblockUI('#mailbox-content');
				}.bind(this)
			});
		},
		render: function() {
			var content = function () {
				if (this.state.id) {
					return ( <MessageContent key={ this.state.view } view={ this.state.view } data={ this.state.data } /> );
				} else {
					return ( <Content key={ this.state.view } state={ this.state } length={ this.props.dataPerPage } loadMessages={ this.loadMessages } refresh={ this.refresh } readMessage={ this.readMessage } /> );
				}
			}.bind(this);
			return (
				<div className="row inbox">
					<div className="col-md-3">
						<MainMenu onBtnClick={ this.changeView } view={ this.state.view } />
					</div>
					<div className="col-md-9">
						<Header title={ this.state.title } search={ (this.state.id) ? false : true } onFilter={ this.filterMessage }  refresh={ this.refresh } />
						{ content() }
					</div>
				</div>
			);
		}
	});

	var MainMenu = React.createClass({
		handleBtnClick: function (event) {
			this.props.onBtnClick($(event.target).data('view'));
		},
		render: function () {
			return (
				<ul className="inbox-nav margin-bottom-10" ref="menu">
					<li className={ (this.props.view == 'inbox') ? 'inbox active' : 'inbox' }>
						<button className="btn" onClick={ this.handleBtnClick } data-view="inbox">
							{ _trans('inbox') }
						</button>
						<b></b>
					</li>
					<li className={ (this.props.view == 'sent') ? 'sent active' : 'sent' }>
						<button className="btn" onClick={ this.handleBtnClick } data-view="sent">
							{ _trans('sent') }
						</button>
						<b></b>
					</li>
					<li className={ (this.props.view == 'trash') ? 'trash active' : 'trash' }>
						<button className="btn" onClick={ this.handleBtnClick } data-view="trash">
							{ _trans('trash') }
						</button>
						<b></b>
					</li>
				</ul>
			);
		}
	});

	var Header = React.createClass({
		render: function () {
			var search = function () {
				if (this.props.search) {
					return ( <SearchBar onFilter={ this.props.onFilter } /> );
				} else {
					return (
						<button onClick={ this.props.refresh } className="btn btn-xs default pull-right">
							<i className="fa fa-minus-square"></i>
							&nbsp;
							{ _trans('close') }
						</button>
					);
				}
			}.bind(this);
			return (
				<div className="inbox-header">
					<h1 className="pull-left">{ this.props.title }</h1>
					{ search() }
				</div>
			);
		}
	});

	var SearchBar = React.createClass({
		handleFilter: function (event) {
			if (('keypress' == event.type && 'Enter' == event.key) || 'click' == event.type) {
				this.props.onFilter(React.findDOMNode(this.refs.filter).value);
			}
		},
		render: function () {
			return (
				<div className="input-group input-medium pull-right">
					<input onKeyPress={ this.handleFilter } ref="filter" type="text" className="form-control" placeholder={ _trans('search') } />
					<span className="input-group-btn">
						<button onClick={ this.handleFilter } type="button" className="btn blue"><i className="fa fa-search"></i></button>
					</span>
				</div>
			);
		}
	});

	var Content = React.createClass({
		componentDidMount: function () {
			Metronic.initUniform();
			_bindCheckAll();
		},
		componentDidUpdate: function (prevProps, prevState) {
			Metronic.initUniform();
		},
		render: function () {
			var actionMenu = function () {
				switch (this.props.state.view) {
					case 'inbox':
						return ( <InboxMenu refresh={ this.props.refresh } /> );
					case 'trash':
						return ( <TrashMenu refresh={ this.props.refresh } /> );
				}
			}.bind(this),
				checkAll = function () {
					if (jQuery.inArray(this.props.state.view, ['inbox', 'trash']) >= 0) {
						return ( <input type="checkbox" className="mail-group-checkbox" /> );
					}
				}.bind(this);
			return (
				<div id="mailbox-content" className="inbox-content">
					<table className="table table-striped table-advance table-hover">
						<thead>
							<tr>
								<th colSpan="3">
									{ checkAll() }
									&nbsp;
									{ actionMenu() }
								</th>
								<Pager state={ this.props.state } length={ this.props.length } loadMessage={ this.props.loadMessages } />
							</tr>
						</thead>
						<MessageList view={ this.props.state.view } data={ this.props.state.data } readMessage={ this.props.readMessage } />
					</table>
				</div>
			);
		}
	});

	var MessageContent = React.createClass({
		render: function () {
			var data = this.props.data,
				avatarStyle = {height: '30px'},
				title = function () {
					if (data.title) {
						return data.title;
					}

					return (
						<em>(&nbsp;{ _trans('no_object') }&nbsp;)</em>
					);
				},
				avatar = function () {
					if (data.avatar_src) {
						return (
							<img src={ data.avatar_src } className="img-circle" style={ avatarStyle } />
						);
					}
				},
				reply = function () {
					if ('sent' != this.props.view) {
						return (
							<a href={ internal.generateUrl('section_id_35_compose', {'username': data.sender_username, 'reply': ('RE: ' + ((data.title) ? data.title : ''))}) } className="btn btn-sm blue-madison">
								<i className="fa fa-reply"></i>
								&nbsp;
								{ _trans('reply') }
							</a>
						);
					}
				}.bind(this),
				message = function () {
					return {__html: data.message};
				};
			return (
				<div id="mailbox-content" className="inbox-content">
					<div className="inbox-header inbox-view-header">
						<h1 className="pull-left">{ title() }</h1>
					</div>
					<div className="inbox-view-info">
						<div className="row">
							<div className="col-md-7">
								{ avatar() }
								&nbsp;
								{ _trans('by') }
								&nbsp;
								<span className="bold">
									<a href={ internal.generateUrl('section_id_35', {'username': data.sender_username}) } >
										{ data.sender }
									</a>
								</span>
								&nbsp;
								{ _trans('sent_on') }
								&nbsp;
								<span className="bold">{ data.date }</span>
							</div>
							<div className="col-md-5 inbox-info-btn">
								{ reply() }
							</div>
						</div>
					</div>
					<div className="inbox-view" dangerouslySetInnerHTML={ message() } />
				</div>
			);
		}
	});

	var InboxMenu = React.createClass({
		handleMarkAsRead: function (event) {
			var $checkboxes = $('#mailbox-content').find('.mail-checkbox'),
				$checkAll = jQuery('.mail-group-checkbox'),
				messageIds = [];

			$checkAll.attr("checked", false);

			$.each($checkboxes, function (index, input) {
				var $checkbox = $(input);
				if ($checkbox.is(':checked')) {
					messageIds.push($checkbox.closest('tr').data('messageid'));
					$checkbox.attr("checked", false);
				}
			});

			if (messageIds.length > 0) {
				$.ajax({
					url: internal.url,
					data: {
						action: 'mark_as_read',
						ids: messageIds
					},
					method: 'POST',
					dataType: 'json',
					cache: false,
					success: function (data) {
						this.props.refresh(function () {
							jQuery.uniform.update($checkAll);
							jQuery.uniform.update($checkboxes);
						});
					}.bind(this),
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
					}.bind(this)
				});
			}
		},
		handleDelete: function () {
			var $checkboxes = $('#mailbox-content').find('.mail-checkbox'),
				$checkAll = jQuery('.mail-group-checkbox'),
				messageIds = [];

			$checkAll.attr("checked", false);

			$.each($checkboxes, function (index, input) {
				var $checkbox = $(input);
				if ($checkbox.is(':checked')) {
					messageIds.push($checkbox.closest('tr').data('messageid'));
					$checkbox.attr("checked", false);
				}
			});

			if (messageIds.length > 0) {
				$.ajax({
					url: internal.url,
					data: {
						action: 'delete',
						ids: messageIds
					},
					method: 'POST',
					dataType: 'json',
					cache: false,
					success: function (data) {
						this.props.refresh(function () {
							jQuery.uniform.update($checkAll);
							jQuery.uniform.update($checkboxes);
						});
					}.bind(this),
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
					}.bind(this)
				});
			}
		},
		render: function () {
			return (
				<div className="btn-group">
					<a className="btn btn-sm grey-silver dropdown-toggle" href="javascript:;" data-toggle="dropdown">
						<i className="fa fa-cogs"></i>
						&nbsp;
						<i className="fa fa-angle-down"></i>
					</a>
					<ul className="dropdown-menu">
						<li>
							<a onClick={ this.handleMarkAsRead } href="javascript:;">
								<i className="fa fa-check-square-o"></i>
								&nbsp;
								{ _trans('mark_as_read') }
							</a>
						</li>
						<li className="divider">
						</li>
						<li>
							<a onClick={ this.handleDelete } href="javascript:;">
								<i className="fa fa-trash-o"></i>
								&nbsp;
								{ _trans('delete') }
							</a>
						</li>
					</ul>
				</div>
			);
		}
	});

	var TrashMenu = React.createClass({
		handleRecover: function (event) {
			var $checkboxes = $('#mailbox-content').find('.mail-checkbox'),
				$checkAll = jQuery('.mail-group-checkbox'),
				messageIds = [];

			$checkAll.attr("checked", false);

			$.each($checkboxes, function (index, input) {
				var $checkbox = $(input);
				if ($checkbox.is(':checked')) {
					messageIds.push($checkbox.closest('tr').data('messageid'));
					$checkbox.attr("checked", false);
				}
			});

			if (messageIds.length > 0) {
				$.ajax({
					url: internal.url,
					data: {
						action: 'recover',
						ids: messageIds
					},
					method: 'POST',
					dataType: 'json',
					cache: false,
					success: function (data) {
						this.props.refresh(function () {
							jQuery.uniform.update($checkAll);
							jQuery.uniform.update($checkboxes);
						});
					}.bind(this),
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
					}.bind(this)
				});
			}
		},
		handleDelete: function (deleteAll) {
			var $checkboxes = $('#mailbox-content').find('.mail-checkbox'),
				$checkAll = jQuery('.mail-group-checkbox'),
				messageIds = [];

			$checkAll.attr("checked", false);

			$.each($checkboxes, function (index, input) {
				var $checkbox = $(input);
				if ($checkbox.is(':checked') || true === deleteAll) {
					messageIds.push($checkbox.closest('tr').data('messageid'));
					$checkbox.attr("checked", false);
				}
			});

			if (messageIds.length > 0) {
				$.ajax({
					url: internal.url,
					data: {
						action: 'delete',
						ids: messageIds
					},
					method: 'POST',
					dataType: 'json',
					cache: false,
					success: function (data) {
						this.props.refresh(function () {
							jQuery.uniform.update($checkAll);
							jQuery.uniform.update($checkboxes);
						});
					}.bind(this),
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
					}.bind(this)
				});
			}
		},
		handleDeleteAll: function () {
			this.handleDelete(true);
		},
		render: function () {
			return (
				<div className="btn-group">
					<a className="btn btn-sm grey-silver dropdown-toggle" href="javascript:;" data-toggle="dropdown">
						<i className="fa fa-cogs"></i>
						&nbsp;
						<i className="fa fa-angle-down"></i>
					</a>
					<ul className="dropdown-menu">
						<li>
							<a onClick={ this.handleRecover } href="javascript:;">
								<i className="fa fa-recycle"></i>
								&nbsp;
								{ _trans('recover') }
							</a>
						</li>
						<li>
							<a onClick={ this.handleDelete } href="javascript:;">
								<i className="fa fa-trash-o"></i>
								&nbsp;
								{ _trans('hard_delete') }
							</a>
						</li>
						<li className="divider">
						</li>
						<li>
							<a onClick={ this.handleDeleteAll } href="javascript:;">
								<i className="fa fa-asterisk"></i>
								&nbsp;
								{ _trans('empty_trash') }
							</a>
						</li>
					</ul>
				</div>
			);
		}
	});

	var Pager = React.createClass({
		handlePrevious: function () {
			this.props.loadMessage(
				this.props.state.view,
				(+this.props.state.page - 1),
				this.props.state.filter
			);
		},
		handleNext: function () {
			this.props.loadMessage(
				this.props.state.view,
				(+this.props.state.page + 1),
				this.props.state.filter
			);
		},
		render: function () {
			var fromPage = ((+this.props.state.page - 1) * this.props.length) + 1,
				previous = function () {
					if (this.props.state.page > 1) {
						return (
							<button onClick={ this.handlePrevious } className="btn btn-sm blue">
								<i className="fa fa-angle-left"></i>
							</button>
						);
					}
				}.bind(this),
				next = function () {
					if ((fromPage + (+this.props.length - 1)) < this.props.state.total) {
						return (
							<button onClick={ this.handleNext } className="btn btn-sm blue">
								<i className="fa fa-angle-right"></i>
							</button>
						);
					}
				}.bind(this);

			return (
				<th className="pagination-control" colSpan="3">
					<span className="pagination-info">
						{ fromPage }-{ fromPage + (+this.props.length - 1) }
						&nbsp;{ _trans('of') }&nbsp;
						{ this.props.state.total }&nbsp;
						<span className="hidden-xs">
							{ (this.props.state.total > 1) ? (_trans('message')+'s') : _trans('message') }
						</span>
					</span>
					{ previous() }
					{ next() }
				</th>
			);
		}
	});

	var MessageList = React.createClass({
		render: function() {
			var messageNodes = this.props.data.map(function (data) {
				if ('sent' == this.props.view) {
					return (
						<MessageSent key={ data.id } data={ data } readMessage={ this.props.readMessage } />
					);
				} else {
					return (
						<Message key={ data.id } data={ data } readMessage={ this.props.readMessage } />
					);
				}
			}.bind(this));
			return (
				<tbody>
					{ messageNodes }
				</tbody>
			);
		}
	});

	var Message = React.createClass({
		handleFlag: function (event) {
			var $flag = $(event.target);

			$flag.toggleClass('fa-starred');

			if ($flag.prop('tagName') == 'I') {
				$.ajax({
					url: internal.url,
					data: {
						action: 'toggle_flag',
						id: $flag.closest('tr').data('messageid')
					},
					method: 'POST',
					dataType: 'json',
					cache: false,
					success: function (data) {
						//console.log(data);
					},
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
					}.bind(this)
				});
			}
		},
		handleRead: function (event) {
			var id = $(event.target).closest('tr').data('messageid'),
				nodeType = event.target.nodeName;

			if ('INPUT' != nodeType && 'I' != nodeType) {
				this.props.readMessage(id);
			}
		},
		render: function () {
			var data = this.props.data,
				title = function () {
					if (data.title) {
						return data.title;
					}

					return (
						<em>(&nbsp;{ _trans('no_object') }&nbsp;)</em>
					);
				};

			return (
				<tr onClick={ this.handleRead } className={ (data.viewed) ? '' : 'unread' } data-messageid={ data.id }>
					<td className="inbox-small-cells">
						<input type="checkbox" className="mail-checkbox" />
					</td>
					<td className="inbox-small-cells">
						<i onClick={ this.handleFlag } className={ 'fa fa-star' + ((data.flag) ? ' fa-starred' : '') }></i>
					</td>
					<td className="hidden-xs">
						{ data.sender }
					</td>
					<td>
						{ title() }
					</td>
					<td className="inbox-small-cells">
						<i className={ 'fa fa-paperclip' + ((data.attachment) ? '' : ' hide') }></i>
					</td>
					<td className="text-right">
						{ data.date }
					</td>
				</tr>
			);
		}
	});

	var MessageSent = React.createClass({
		handleRead: function (event) {
			var id = $(event.target).closest('tr').data('messageid'),
				nodeType = event.target.nodeName;

			if ('INPUT' != nodeType && 'I' != nodeType) {
				this.props.readMessage(id);
			}
		},
		render: function () {
			var data = this.props.data,
				title = function () {
					if (data.title) {
						return data.title;
					}

					return (
						<em>(&nbsp;{ _trans('no_object') }&nbsp;)</em>
					);
				};

			return (
				<tr onClick={ this.handleRead } className={ (data.viewed) ? '' : 'unread' } data-messageid={ data.id }>
					<td>
						<i className={ (data.viewed) ? 'icon-envelope-letter' : 'icon-envelope' } />
					</td>
					<td>
						{ data.recipient }
					</td>
					<td>
						{ title() }
					</td>
					<td className="inbox-small-cells">
						<i className={ 'fa fa-paperclip' + ((data.attachment) ? '' : ' hide') }></i>
					</td>
					<td className="text-right" colSpan="2">
						{ data.date }
					</td>
				</tr>
			);
		}
	});

	// PUBLIC

	return {

		init: function () {

			ReactDOM.render(
				<MailBox dataPerPage="20" />,
				document.getElementById('mailbox')
			);
		}
	};

}();