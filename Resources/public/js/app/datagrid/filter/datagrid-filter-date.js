/**
 * Date filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridFilterDate
 * @extends OroApp.DatagridFilterChoice
 */
OroApp.DatagridFilterDate = OroApp.DatagridFilterChoice.extend({
    /**
     * Template for filter criteria
     *
     * @property {function(Object, ?Object=): String}
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div>' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
                '<% }); %>' +
                '</div>' +
            '<div>' +
                'date from <input type="text" name="start" value="" style="width:80px;" />' +
                'to <input type="text" name="end" value="" style="width:80px;" />' +
            '</div>' +
            '<div class="btn-group">' +
                '<button class="btn btn-mini filter-update">Update</button>' +
                '<button class="btn btn-mini filter-criteria-hide">Close</button>' +
            '</div>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        type: 'input[name="type"]:checked',
        value_start: 'input[name="start"]',
        value_end: 'input[name="end"]'
    },

    /**
     * Check if filter contain value
     *
     * @return {Boolean}
     */
    hasValue: function() {
        return this.$(this.parameterSelectors.value_start).val() != ''
            || this.$(this.parameterSelectors.value_end).val() != '';
    },

    /**
     * Reset filter form elements
     *
     * @return {*}
     */
    reset: function() {
        this.$(this.parameterSelectors.type).val('');
        this.$(this.parameterSelectors.value_start).val('');
        this.$(this.parameterSelectors.value_end).val('');
        return this;
    },

    /**
     * Get list of filter parameters
     *
     * @return {Object}
     */
    getParameters: function() {
        return {
            '[type]':  this.$(this.parameterSelectors.type).val(),
            '[value][start]': this.$(this.parameterSelectors.value_start).val(),
            '[value][end]': this.$(this.parameterSelectors.value_end).val()
        };
    }
});
