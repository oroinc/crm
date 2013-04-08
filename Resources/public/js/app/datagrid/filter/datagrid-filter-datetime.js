/**
 * Datetime filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridFilterDateTime
 * @extends OroApp.DatagridFilterDate
 */
OroApp.DatagridFilterDateTime = OroApp.DatagridFilterDate.extend({
    /**
     * CSS class for visual datetime input elements
     *
     * @property
     */
    inputClass: 'datetime-visual-element',

    /**
     * Datetime widget options
     *
     * @property
     */
    dateWidgetOptions:_.extend({
        timeFormat: "HH:mm",
        altFieldTimeOnly: false,
        altSeparator: ' ',
        altTimeFormat: 'HH:mm'
    }, OroApp.DatagridFilterDate.prototype.dateWidgetOptions),

    /**
     * Additional datetime widget options that might be passed to filter
     * http://api.jqueryui.com/datepicker/
     * http://trentrichardson.com/examples/timepicker/#tp-options
     *
     * @property
     */
    externalWidgetOptions: {},

    /**
     * Initialize datetime widget
     *
     * @param {String} visualSelector
     * @param {String} actualSelector
     * @return {*}
     * @protected
     */
    _initializeDateWidget: function(visualSelector, actualSelector) {
        var options = _.extend({
            altField: actualSelector
        }, this.dateWidgetOptions);

        this.$(visualSelector).datetimepicker(options);
        var widget = this.$(visualSelector).datetimepicker('widget');
        widget.addClass(this.dateWidgetOptions.className);

        return widget;
    },

    /**
     * Convert actual date values to visual fields
     *
     * @protected
     */
    _convertActualToVisual: function() {
        this._convertDateData(
            this.criteriaValueSelectors.value,
            this.criteriaValueSelectors.visualValue,
            this.dateWidgetOptions.altFormat,
            this.dateWidgetOptions.dateFormat,
            this.dateWidgetOptions.altTimeFormat,
            this.dateWidgetOptions.timeFormat
        );
    },

    /**
     * Convert visual date values to actual fields
     *
     * @protected
     */
    _convertVisualToActual: function() {
        this._convertDateData(
            this.criteriaValueSelectors.visualValue,
            this.criteriaValueSelectors.value,
            this.dateWidgetOptions.dateFormat,
            this.dateWidgetOptions.altFormat,
            this.dateWidgetOptions.timeFormat,
            this.dateWidgetOptions.altTimeFormat
        );
    },

    /**
     * Convert data procedure
     *
     * @protected
     */
    _convertDateData: function(fromSelectors, toSelectors, fromDateFormat, toDateFormat, fromTimeFormat, toTimeFormat) {
        _.each(fromSelectors, function(fromSelector, name) {
            var toSelector = toSelectors[name];
            var fromValue = this._getInputValue(fromSelector);
            var toValue = '';
            if (fromValue) {
                var dateValue = $.datepicker.formatDate(toDateFormat, $.datepicker.parseDate(fromDateFormat, fromValue));

                // remove date part
                var dateBefore = $.datepicker.formatDate(fromDateFormat, $.datepicker.parseDate(toDateFormat, dateValue));
                fromValue = fromValue.substr(dateBefore.length + this.dateWidgetOptions.altSeparator.length);

                var timeValue = $.datepicker.formatTime(toTimeFormat, $.datepicker.parseTime(fromTimeFormat, fromValue));
                toValue = dateValue + this.dateWidgetOptions.altSeparator + timeValue;
            }
            this._setInputValue(toSelector, toValue);
        }, this);
    }
});
