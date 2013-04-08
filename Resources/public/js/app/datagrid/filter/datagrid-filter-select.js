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
        '<div class="btn filter-select filter-criteria-selector">' +
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
     * Selector for widget button
     *
     * @property
     */
    buttonSelector: '.select-filter-widget.ui-multiselect:first',

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
        classes: 'select-filter-widget'
    },

    /**
     * Filter value object
     *
     * @property
     */
    emptyValue: {
        value: ''
    },

    /**
     * Select widget menu opened flag
     *
     * @property
     */
    selectDropdownOpened: false,

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
        this.selectWidget = new OroApp.MultiSelectDecorator(this.$(this.inputSelector), _.extend({
            noneSelectedText: this.placeholder,
            selectedText: $.proxy(function(numChecked, numTotal, checkedItems) {
                return this._getSelectedText(checkedItems);
            }, this),
            position: {
                my: 'left top+2',
                at: 'left bottom',
                of: this.$(this.containerSelector)
            },
            open: $.proxy(function() {
                this.selectWidget.onOpenDropdown();
                this._setDropdownWidth();
                this._setButtonPressed(this.$(this.containerSelector), true);
                this.selectDropdownOpened = true;
            }, this),
            close: $.proxy(function() {
                this._setButtonPressed(this.$(this.containerSelector), false);
                setTimeout($.proxy(function() {
                    this.selectDropdownOpened = false;
                }, this), 100);
            }, this)
        }, this.widgetOptions));

        this.selectWidget.setViewDesign(this);
        this.$(this.buttonSelector).append('<span class="caret"></span>');
    },

    /**
     * Get text for filter hint
     *
     * @param {Array} checkedItems
     * @protected
     */
    _getSelectedText: function(checkedItems) {
        if (_.isEmpty(checkedItems)) {
            return this.placeholder;
        }

        var elements = [];
        _.each(checkedItems, function(element) {
            var title = element.getAttribute('title');
            if (title) {
                elements.push(title);
            }
        });
        return elements.join(', ');
    },

    /**
     * Set design for select dropdown
     *
     * @protected
     */
    _setDropdownWidth: function() {
        if (!this.minimumWidth) {
            this.minimumWidth = this.selectWidget.getMinimumDropdownWidth();
        }
        var widget = this.selectWidget.getWidget();
        var filterWidth = this.$(this.containerSelector).width();
        var requiredWidth = Math.max(filterWidth + 10, this.minimumWidth);
        widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
        widget.find('input[type="search"]').width(requiredWidth - 22);
    },

    /**
     * Open/close select dropdown
     *
     * @param {Event} e
     * @protected
     */
    _onClickFilterArea: function(e) {
        if (!this.selectDropdownOpened) {
            this.selectWidget.multiselect('open');
        } else {
            this.selectWidget.multiselect('close');
        }

        e.stopPropagation();
    },

    /**
     * Triggers change data event
     *
     * @protected
     */
    _onSelectChange: function() {
        // set value
        var value = this.getValue();
        this._confirmValue(value);

        // update dropdown
        var widget = this.$(this.containerSelector);
        this.selectWidget.updateDropdownPosition(widget);
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
     * Confirm filter value
     *
     * @protected
     */
    _confirmValue: function(value) {
        if (!_.isEqual(this.confirmedValue.value, value.value)) {
            this.confirmedValue = _.clone(value);
            this.$(this.inputSelector).val(this.confirmedValue.value);
            this.selectWidget.multiselect('refresh');
            this.trigger('update');
        }
    }
});
