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

		this.addEvents();
	}

	DataGrid.prototype = {
		switcher: function(el) {
			if(el.hasClass('disabled')) {
				el.removeClass('disabled');
				el.text(1);
			}
			else {
				el.addClass('disabled');
				el.text(0);
			}
		},
		__addSwitcherEvent: function() {
			var that = this;
			$('body').on('click', this.selector + ' .switcher', function(ev) {
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
			$('body').on('submit', this.selector + ' .filter_form', function(ev) {
				ev.preventDefault();

				var el = $(this),
					action = el.attr('action'),
					search = el.find('.searchFormGrid').val(),
					data = el.serialize();

				$.post(action, data, function(html){
					$($(this).parent(that.selector).data('update')).html(html);
				});
			});
		},
		addEvents: function() {
			this.__addSwitcherEvent();
		}
	};
})();

$(document).ready(function() {
	new DataGrid();
});