var TutoriuxLog = function() {

	// PRIVATE

	var internal = {
		locale: (typeof session_locale !== 'undefined') ? session_locale : 'en',
		url: Routing.generate(((typeof session_locale !== 'undefined') ? session_locale : 'en') + '__RG__section_id_35_activities'),
		translations: {
			processing: {en: 'Processing ...', fr: 'Traitement ...'},
			see_more: {en: 'See more', fr: 'Voir plus'}
		}
	};

	var _trans = function (token) {
		return internal.translations[token][internal.locale];
	};

	var LogBox = React.createClass({
		getInitialState: function() {
			return {
				data: [],
				page: 1,
				total: 0
			};
		},
		componentDidMount: function() {
			$.ajax({
				url: internal.url,
				data: {
					length: this.props.dataPerPage,
					page: this.state.page,
					filter: (this.props.filter) ? this.props.filter : null
				},
				dataType: 'json',
				cache: false,
				success: function(data) {
					this.setState({
						data: this.state.data.concat(data.data),
						page: data.page,
						total: data.total
					});
				}.bind(this),
				error: function(xhr, status, err) {
					console.error(internal.url, status, err.toString());
				}.bind(this)
			});
		},
		loadLogs: function() {
			if (((this.state.page - 1) * this.props.dataPerPage) < this.state.total) {
				Metronic.blockUI({
					target: '#' + this.props.parentId,
					boxed: true,
					message: _trans('processing')
				});
				$.ajax({
					url: internal.url,
					data: {
						length: this.props.dataPerPage,
						page: (+this.state.page) + 1,
						filter: (this.props.filter) ? this.props.filter : null
					},
					dataType: 'json',
					cache: false,
					success: function(data) {
						this.setState({
							data: this.state.data.concat(data.data),
							page: data.page,
							total: data.total
						});
						Metronic.unblockUI('#' + this.props.parentId);
					}.bind(this),
					error: function(xhr, status, err) {
						console.error(internal.url, status, err.toString());
						Metronic.unblockUI('#' + this.props.parentId);
					}.bind(this)
				});
			}
		},
		render: function() {
			var moreBtn = function() {
				if (this.state.page < this.state.total/this.props.dataPerPage){
					return (<MoreBtn onMoreBtnClick={this.loadLogs} />);
				}
			}.bind(this);
			return (
				<div>
					<LogList data={this.state.data} />
					{moreBtn()}
				</div>
			);
		}
	});

	var LogList = React.createClass({
		render: function() {
			var logNodes = this.props.data.map(function (data) {
				var log;
				if (data.url) {
					log = <li key={ data.id }><LinkLog data={data} /></li>;
				} else {
					log = <li key={ data.id }><Log data={data} /></li>;
				}
				return (
					log
				);
			});
			return (
				<ul className="feeds">
					{logNodes}
				</ul>
			);
		}
	});

	var LinkLog = React.createClass({
		render: function() {
			return (
				<a href={this.props.data.url}>
					<Log data={this.props.data} />
				</a>
			);
		}
	});

	var Log = React.createClass({
		render: function() {
			return (
				<div>
					<div className="col1">
						<div className="cont">
							<div className="cont-col1">
								<div className={'label label-sm label-'+((this.props.data.iconColor) ? this.props.data.iconColor : 'primary' )}>
									<i className={(this.props.data.icon) ? this.props.data.icon : 'fa fa-bell-o' }></i>
								</div>
							</div>
							<div className="cont-col2">
								<div className="desc">
									{this.props.data.text}
								</div>
							</div>
						</div>
					</div>
					<div className="col2">
						<div className="date">
							{this.props.data.date}
						</div>
					</div>
				</div>
			);
		}
	});

	var MoreBtn = React.createClass({
		handleBtnClick: function () {
			this.props.onMoreBtnClick();
		},
		render: function() {
			return (
				<div style={{marginTop: '20px'}}>
					<button className="btn default btn-block" onClick={this.handleBtnClick}>
						{ _trans('see_more') }
						<i className="fa fa-history" style={{paddingLeft: '5px'}}></i>
					</button>
				</div>
			);
		}
	});

	// PUBLIC

	return {

		initLogFeed: function () {

			ReactDOM.render(
				<LogBox dataPerPage="20" parentId="tab-all-logs" />,
				document.getElementById('tab-all-logs').getElementsByClassName('scroller')[0]
			);

			ReactDOM.render(
				<LogBox dataPerPage="20" parentId="tab-personal-logs" filter="personal" />,
				document.getElementById('tab-personal-logs').getElementsByClassName('scroller')[0]
			);
		}
	};

}();