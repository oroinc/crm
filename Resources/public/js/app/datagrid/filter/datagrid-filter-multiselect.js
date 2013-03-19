/**
 * Multiple select filter: filter values as multiple select options
 *
 * @class   OroApp.DatagridFilterMultiSelect
 * @extends OroApp.DatagridFilterSelect
 */
OroApp.DatagridFilterMultiSelect = OroApp.DatagridFilterSelect.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: ' +
            '<select style="width:150px;" multiple>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<a href="#" class="disable-filter" />' +
            '<span class="caret"></span>' +
        '</div>'
    )
});
