/**
 * Select filter: filter value as select option
 *
 * @class   OroApp.DatagridFilterSelect
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterSelect = OroApp.DatagridFilter.extend({
    /**
     * Filter template
     *
     * @property
     */
    template: _.template(
        '<div class="btn filter-select">' +
            '<%= label %>: ' +
            '<select>' +
                '<option value=""><%= placeholder %></option>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
        '</div>' +
        '<a href="#" class="disable-filter"><i class="icon-remove hide-text">Close</i></a>'
    ),

    /**
     * Filter content options
     *
     * @property
     */
    options: {},

    /**
     * Value that was confirmed and processed.
     *
     * @property {Object}
     */
    confirmedValue: {},

    /**
     * Placeholder for default value
     *
     * @property
     */
    placeholder: 'All',

    /**
     * Selector for filter area
     *
     * @property
     */
    containerSelector: '.filter-select',

    /**
     * Selector for close button
     *
     * @property
     */
    disableSelector: '.disable-filter',

    /**
     * Selector for select input element
     *
     * @property
     */
    inputSelector: 'select',

    /**
     * Select widget object
     *
     * @property
     */
    selectWidget: null,

    /**
     * Minimum widget menu width, calculated depends on filter options
     *
     * @property
     */
    minimumWidth: null,

    /**
     * Select widget options
     *
     * @property
     */
    widgetOptions: {
        multiple: false,
        height: 'auto',
        selectedList: 1
    },

    /**
     * Filter events
     *
     * @property
     */
    events: {
        'click .filter-select': '_onClickFilterArea',
        'click .disable-filter': '_onClickDisableFilter',
        'change select': '_onSelectChange'
    },

    /**
     * Render filter template
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                label: this.label,
                options: this.options,
                placeholder: this.placeholder
            })
        );

        this._initializeSelectWidget();

        return this;
    },

    /**
     * Initialize multiselect widget
     *
     * @protected
     */
    _initializeSelectWidget: function() {
        this.selectWidget = this.$(this.inputSelector);

        this.selectWidget.multiselect(_.extend({
            classes: 'select-filter-widget',
            position: {
                my: 'left top+2',
                at: 'left bottom',
                of: this.$(this.containerSelector)
            },
            open: $.proxy(function() {
                this._setDropdownDesign();
                var widget = this.selectWidget.multiselect('widget');
                widget.find('input[type="search"]').focus();
                $('body').trigger('click');
            }, this)
        }, this.widgetOptions));

        this.selectWidget.multiselectfilter({
            label: '',
            placeholder: '',
            autoReset: true
        });

        // fix CSS classes
        this.$('.select-filter-widget').removeClass('ui-widget').removeClass('ui-state-default');
        this.$('.select-filter-widget').find('span.ui-icon').remove();
        this.$('.select-filter-widget.ui-multiselect').append('<span class="caret"></span>');
    },

    /**
     * Get element width
     *
     * @param {Object} element
     * @return {Integer}
     * @protected
     */
    _getTextWidth: function(element) {
        var html_org = element.html();
        var html_calc = '<span>' + html_org + '</span>';
        element.html(html_calc);
        var width = element.find('span:first').width();
        element.html(html_org);
        return width;
    },

    /**
     * Set design for select dropdown
     *
     * @protected
     */
    _setDropdownDesign: function() {
        var widget = this.selectWidget.multiselect('widget');

        // calculate minimum width
        if (!this.minimumWidth) {
            var elements = widget.find('.ui-multiselect-checkboxes li');
            _.each(elements, function(element, index, list) {
                var width = this._getTextWidth($(element).find('label'));
                if (width > this.minimumWidth) {
                    this.minimumWidth = width;
                }
            }, this);

            this.minimumWidth += 16;
        }

        // set elements width
        var filterWidth = this.$(this.containerSelector).width();
        var requiredWidth = Math.max(filterWidth + 8, this.minimumWidth);
        widget.width(requiredWidth);
        widget.find('input[type="search"]').width(requiredWidth - 22);

        // fix CSS classes
        widget.addClass('dropdown-menu');
        widget.removeClass('ui-widget-content');
        widget.removeClass('ui-widget');
        widget.find('.ui-widget-header').removeClass('ui-widget-header');
        widget.find('.ui-multiselect-filter').removeClass('ui-multiselect-filter');
        widget.find('ul li label').removeClass('ui-corner-all');
    },

    /**
     * Open select dropdown
     *
     * @protected
     */
    _onClickFilterArea: function() {
        this.selectWidget.multiselect('open');
    },

    /**
     * Triggers change data event
     *
     * @protected
     */
    _onSelectChange: function() {
        var value = this.getValue();
        this._confirmValue(value);
    },

    /**
     * Handle click on filter disabler
     *
     * @param {Event} e
     */
    _onClickDisableFilter: function(e) {
        e.preventDefault();
        this.disable();
    },

    /**
     * Set value to filter's criteria and confirm it
     *
     * @param value
     * @return {*}
     */
    setValue: function(value) {
        this._confirmValue(value);
        return this;
    },

    /**
     * Get confirmed value of filter's criteria
     *
     * @return {Object}
     */
    getValue: function() {
        return {
            value: this.$(this.inputSelector).val()
        };
    },

    /**
     * Set filter parameters
     *
     * @deprecated
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        var value = {
            value: parameters['[value]']
        };
        this.setValue(value);
        return this;
    },

    /**
     * Get filter parameters
     *
     * @deprecated
     * @return {Object}
     */
    getParameters: function() {
        var value = this.getValue();
        return {
            '[value]': value.value
        };
    },

    /**
     * Confirm filter value
     *
     * @protected
     */
    _confirmValue: function(value) {
        if (this.confirmedValue.value != value.value) {
            this.confirmedValue = _.clone(value);
            this.$(this.inputSelector).val(this.confirmedValue.value);
            this.selectWidget.multiselect('refresh');
            this.trigger('update');
        }
    },

    /**
     * Reset filter value
     *
     * @return {*}
     */
    reset: function() {
        this.setValue({
            value: ''
        });
        return this;
    }
});
