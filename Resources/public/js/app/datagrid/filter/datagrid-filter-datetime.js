/**
 * Datetime filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridFilterDateTime
 * @extends OroApp.DatagridFilterDate
 */
OroApp.DatagridFilterDateTime = OroApp.DatagridFilterDate.extend({
    /**
     * Template for filter criteria
     *
     * @property {function(Object, ?Object=): String}
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div>' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<input type="radio" name="<%= name %>" value="<%= value %>" /><%= hint %>' +
                '<% }); %>' +
            '</div>' +
            '<div>' +
                'datetime from <input type="text" name="start" value="" style="width:80px;" />' +
                'to <input type="text" name="end" value="" style="width:80px;" />' +
            '</div>' +
            '<div class="btn-group">' +
                '<button class="btn btn-mini filter-update">Update</button>' +
                '<button class="btn btn-mini filter-criteria-hide">Close</button>' +
            '</div>' +
        '</div>'
    )
});
