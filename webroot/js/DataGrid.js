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

		this.addEvents();
	}

	DataGrid.prototype = {
		switcher: function(el) {
			if(el.hasClass('disabled')) {
				el.removeClass('disabled');
				el.text(el.data('enabled_label'));
			}
			else {
				el.addClass('disabled');
				el.text(el.data('disabled_label'));
			}
		},
		__addSwitcherEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' .switcher', function(ev) {
				ev.preventDefault();

				if($(this).attr('href') && $(this).attr('href') != '#') {
					$.post($(this).attr('href'), $.proxy(function() {
						that.switcher($(this));
					},this));
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
					$(that.get(this).data('update')).html(html);
				});
			});
		},
		__addPaginationEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' .pagination a', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$(that.get(this).data('update')).html(data);
				});
			});
		},
		__addSortEvent: function() {
			var that = this;
			this.body.on('click', this.selector + ' .sort', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$(that.get(this).data('update')).html(data);
				});
			});
		},
		__addConfirmEvent: function() {
			this.body.on('click', this.selector + ' .confirm_message', function() {
				return confirm($(this).data('confirm_message'));
			});
		},
		__addExpandRowEvent: function() {
			$(this.selector + ' tr[data-depth]').css('cursor', 'pointer');

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
				if(ev.target.nodeName.toLowerCase() != 'a') {
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
		},
		get: function(el) {
			return $($(el).parent(this.selector));
		},
		addEvents: function() {
			if(this.element.data('ajax')) {
				this.__addSwitcherEvent();
				this.__addSortEvent();
				this.__addPaginationEvent();
				this.__addFilterEvent();
			}

			this.__addConfirmEvent();
			this.__addExpandRowEvent();
		}
	};

	return DataGrid;
})();

$(document).ready(function() {
	new DataGrid();
});