/**
 * Select filter: filter value as select option
 *
 * @class   OroApp.DatagridFilterSelect
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterSelect = OroApp.DatagridFilter.extend({
    /** @property */
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

    /** @property */
    options: {},

    /**
     * Value that was confirmed and processed.
     *
     * @property {Object}
     */
    confirmedValue: {},

    /** @property */
    placeholder: 'All',

    inputSelector: 'select',

    /** @property */
    events: {
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

        return this;
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
