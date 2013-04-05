/**
 * Multiple select filter: filter values as multiple select options
 *
 * @class   OroApp.DatagridFilterMultiSelect
 * @extends OroApp.DatagridFilterSelect
 */
OroApp.DatagridFilterMultiSelect = OroApp.DatagridFilterSelect.extend({
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
     * Fix menu design on data change (after click on checkbox)
     *
     * @protected
     */
    _onSelectChange: function() {
        OroApp.DatagridFilterSelect.prototype._onSelectChange.apply(this, arguments);
        this._setDropdownWidth();
    }
});
