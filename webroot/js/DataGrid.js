var DataGrid;

DataGrid = (function() {
/**
 * Constructor
 */
	function DataGrid(selector) {
		if(typeof selector == 'undefined') {
			selector = '.data_grid';
		}

		this.selector = selector;
		this.element = $(selector);
		this.body = $('body');
		// jQuery selector of elements that should be clickable in a table row
		this.enabledElements = 'input, a, a img';

		$.cookie.json = true;

		this.addEvents();

		this.__loadExpandStates();
	}

	DataGrid.prototype = {
		switcher: function(el) {
			if(el.hasClass('disabled')) {
				el.addClass('enabled').removeClass('disabled');
				el.text(el.data('enabled_label'));
			}
			else {
				el.removeClass('enabled').addClass('disabled');
				el.text(el.data('disabled_label'));
			}
		},
		/**
		 * attach and handle switch event
		 * @todo show formatted message instead of
		 * @return {void}
		 */
		__addSwitcherEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' .switcher', function(ev) {
				ev.preventDefault();

				if($(this).attr('href') && $(this).attr('href') != '#') {
					var xhr = $.post($(this).attr('href'),
						$.proxy(function(data, textStatus, jqXHR) {
							that.switcher($(ev.target));
							that.__gridUpdated();
						}, this));

					xhr.done(function(data, textStatus, jqXHR){
						// Success message
						if (jqXHR.responseText) {
							alert(jqXHR.responseText);
						}
					});

					xhr.fail(function(jqXHR){
						// Error message
						if (jqXHR.responseText) {
							alert(jqXHR.responseText);
						}
					});
				}
				else {
					that.switcher($(this));
				}
			});
		},
		__addFilterEvent: function() {
			var that = this;
			this.body.on('submit', this.selector + ' .filter_form', function(ev) {
				ev.preventDefault();

				var el = $(this),
					action = el.attr('action'),
					data = el.serialize();

				$.post(action, data, function(html){
					$(that.get(ev.target).data('update')).html(html);

					that.__gridUpdated();
				});
			});
		},
		__addPaginationEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' .pagination a', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$(that.get(ev.target).data('update')).html(data);

					that.__gridUpdated();
				});
			});
		},
		__addSortEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' a.sort', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$(that.get(ev.target).data('update')).html(data);

					that.__gridUpdated();
				});
			});
		},
		__addRowActionEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' tr[data-action]', function(ev) {
				if ($(ev.target).is(that.enabledElements)) {
					return;
				}

				ev.preventDefault();

				window.location = ($(this).data('action'));
			});
		},
		__addConfirmEvent: function() {
			this.body.on('click', this.selector + ' .confirm_message', function() {
				return confirm($(this).data('confirm_message'));
			});
		},
		__addExpandRowEvent: function() {
			//$(this.selector + ' tr[data-depth]').css('cursor', 'pointer');

			$(this.selector + ' tr[data-depth]').filter(function() {
				return $(this).data('depth') > 0;
			}).hide();

			$(this.selector + ' tr[data-depth]').each(function() {
				if($(this).data('depth') < $(this).next().data('depth')) {
					$(this).addClass('expandable');
				}
			});

			var that = this;
			this.body.on('click', this.selector + ' tr[data-depth]', function(ev) {
				if(!$(ev.target).is(that.enabledElements)) {
					ev.preventDefault();

					that.__rowExpandToggle($(this));
				}
			});
		},
		__rowExpandToggle: function(el) {
			var nextDepth = el.data('depth') + 1,
				next = el.next('tr[data-depth=' + nextDepth + ']'),
				hide = false;

			el.removeClass('collapsed').addClass('expanded');
			if(next.is(':visible')) {
				hide = true;
				el.addClass('collapsed').removeClass('expanded');
			}

			if(next.length === 0) {
				el.removeClass('collapsed').removeClass('expanded');
			}

			var checkDepth = function() {
				return $(this).data('depth') >= nextDepth;
			};

			while(next.length > 0) {
				if(!hide && next.data('depth') == nextDepth) {
					next.show();
					next.removeClass('collapsed').addClass('expanded');
				}
				else {
					next.hide();
					next.addClass('collapsed').removeClass('expanded');
				}

				if(next.next().length === 0 || next.next().data('depth') <= next.data('depth') + 1) {
					next.removeClass('collapsed').removeClass('expanded');
				}

				next = next.next('tr[data-depth]').filter(checkDepth);
			}

			this.__saveExpandState(el);
		},
/**
 * Save expanded state to a cookie
 *
 * @param {DomElement} el Row element
 *
 * @return {boolean} If the state is saved to the cookie
 */
		__saveExpandState: function(el) {
			var elId = el.attr('id');

			if (elId) {
				var expandStates = $.cookie('DataGridder.expand_states');

				if(!expandStates) {
					expandStates = {};
				}

				expandStates[elId] = el.hasClass('expanded') ? 'expanded' : 'collapsed';

				$.cookie('DataGridder.expand_states', expandStates);

				return true;
			}

			return false;
		},
/**
 * Load expand states from cookie
 *
 * @return {boolean} If the expand states are loaded
 */
		__loadExpandStates: function() {
			var expandStates = $.cookie('DataGridder.expand_states'),
				nextDepth,
				el,
				next,
				checkDepth = function() {
					return $(this).data('depth') >= nextDepth;
				},
				isVisible = function(el) {
					var parents = [],
						prev = el.prev();

					while(prev.data('depth') > 0) {
						if(prev.data('depth') < el.data('depth')) {
							parents.push(prev);
						}

						prev = prev.prev();
					}
					if(prev.data('depth') != el.data('depth')) {
						parents.push(prev);
					}

					for(var i=0;i<parents.length;i++) {
						if(expandStates[parents[i].attr('id')] != 'expanded') {
							return false;
						}
					}

					return true;
				};

			if(expandStates) {
				for (var elId in expandStates) {
					el = $('#' + elId);
					nextDepth = el.data('depth') + 1;
					next = el.next('tr[data-depth=' + nextDepth + ']');

					while(next.length > 0) {
						if(nextDepth == $(next).data('depth')) {
							next.hide();
							if(expandStates[el.attr('id')] == 'expanded' && isVisible(next)) {
								next.show();
							}
						}

						next = next.next('tr[data-depth]').filter(checkDepth);
					}

					el.addClass(expandStates[elId]);
				}

				return true;
			}

			return false;
		},
		__addLimitEvent: function() {
			var that = this;
			this.body.on('change', this.selector + ' .limit select', function(ev) {
				$.get(location.href + '?limit=' + $(this).val(), function(data) {
					$(that.get(ev.target).data('update')).html(data);

					that.__gridUpdated();
				});
			});
		},
		__gridUpdated: function() {
			$(this.selector).trigger('gridupdated');
		},
		__addGridColumnFilter: function() {
			if($('.column-filter-options',this.selector).is(':visible')) {
				$('.column-filter-options',this.selector).hide();
			}

			this.body.on('click', this.selector + ' .column-filter', function(ev) {
				ev.preventDefault();
				$(this).next('.column-filter-options').toggle();
			});

			var that = this;
			this.body.on('click', this.selector + ' .column-filter-options a', function(ev) {
				ev.preventDefault();

				var object = {};
				object['data[DataGridColumnFilter][' + $(this).data('field') + ']'] = $(this).data('key');

				$.post(location.href, object, function(data) {
					$(that.get(ev.target).data('update')).html(data);

					that.__gridUpdated();
				});
			});
		},
		get: function(el) {
			return $(el).parents(this.selector).first();
		},
		addEvents: function() {
			if(this.element.data('ajax')) {
				this.__addSwitcherEvent();
				this.__addSortEvent();
				this.__addPaginationEvent();
				this.__addFilterEvent();
			}

			this.__addGridColumnFilter();

			this.__addConfirmEvent();
			this.__addRowActionEvent();
			this.__addExpandRowEvent();

			this.__addLimitEvent();

			this.__gridUpdated();
		}
	};

	return DataGrid;
})();

$(document).ready(function() {
	new DataGrid();
});
