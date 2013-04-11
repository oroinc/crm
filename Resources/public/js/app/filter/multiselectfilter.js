var OroApp = OroApp || {};
OroApp.Filter = OroApp.Filter || {};

/**
 * Multiple select filter: filter values as multiple select options
 *
 * @class   OroApp.Filter.MultiSelectFilter
 * @extends OroApp.Filter.SelectFilter
 */
OroApp.Filter.MultiSelectFilter = OroApp.Filter.SelectFilter.extend({
    /**
     * Multiselect filter template
     *
     * @property
     */
    template: _.template(
        '<div class="btn filter-select filter-criteria-selector">' +
            '<%= label %>: ' +
            '<select multiple>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
        '</div>' +
        '<a href="#" class="disable-filter"><i class="icon-remove hide-text">Close</i></a>'
    ),

    /**
     * Select widget options
     *
     * @property
     */
    widgetOptions: {
        multiple: true,
        classes: 'select-filter-widget multiselect-filter-widget'
    },

    /**
     * @inheritDoc
     */
    _onSelectChange: function() {
        OroApp.Filter.SelectFilter.prototype._onSelectChange.apply(this, arguments);
        this._setDropdownWidth();
    }
});
