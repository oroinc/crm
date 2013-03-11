/**
 * Datetime filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridFilterDateTime
 * @extends OroApp.DatagridFilterDate
 */
OroApp.DatagridFilterDateTime = OroApp.DatagridFilterDate.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>:' +
            '<% _.each(choices, function (hint, value) { %>' +
            '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
            '<% }); %>' +
            'datetime from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter" />' +
            '<span class="caret"></span>' +
            '</div>'
    )
});
