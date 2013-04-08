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
                '<input type="hidden" name="start" value="" /> ' +
                '<input type="hidden" name="end" value="" />' +
                'from <input type="text" name="start_visual" value="" class="<%= inputClass %>" /> ' +
                'to <input type="text" name="end_visual" value="" class="<%= inputClass %>" />' +
            '</div>' +
            '<div class="horizontal">' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<div class="oro-clearfix">' +
                        '<input type="radio" id="<%= name %>-<%= value %>" name="<%= name %>" value="<%= value %>" /><label for ="<%= name %>-<%= value %>"><%= hint %></label>' +
                    '</div>' +
                '<% }); %>' +
                '<br/>' +
            '</div>' +
            '<div class="oro-action">' +
                '<div class="btn-group">' +
                    '<button class="btn btn-small filter-criteria-hide">Close</button>' +
                    '<button class="btn btn-small btn-primary filter-update">Update</button>' +
                '</div>' +
            '</div>' +
        '</div>'
    ),

    /**
     * Selectors for filter data
     *
     * @property
     */
    criteriaValueSelectors: {
        type: 'input[type="radio"]',
        value: {
            start: 'input[name="start"]',
            end:   'input[name="end"]'
        },
        visualValue: {
            start: 'input[name="start_visual"]',
            end:   'input[name="end_visual"]'
        }
    },

    /**
     * Empty data object
     *
     * @property
     */
    emptyValue: {
        type: '',
        value: {
            start: '',
            end: ''
        }
    },

    /**
     * CSS class for visual date input elements
     *
     * @property
     */
    inputClass: 'date-visual-element',

    /**
     * Date widget options
     *
     * @property
     */
    dateWidgetOptions: {
        changeMonth: true,
        changeYear:  true,
        yearRange:  '-50:+1',
        dateFormat: 'yy-mm-dd',
        altFormat:  'yy-mm-dd',
        class:      'date-filter-widget',
        showButtonPanel: true,
        currentText: 'Now'
    },

    /**
     * Additional date widget options that might be passed to filter
     * http://api.jqueryui.com/datepicker/
     *
     * @property
     */
    externalWidgetOptions: {},

    /**
     * References to date widgets
     *
     * @property
     */
    dateWidgets: {
        start: null,
        end: null
    },

    /**
     * Date filter type values
     *
     * @property
     */
    typeValues: {
        between:    1,
        notBetween: 2
    },

    /**
     * Date widget selector
     *
     * @property
     */
    dateWidgetSelector: 'div#ui-datepicker-div.ui-datepicker',

    /**
     * @inheritDoc
     */
    initialize: function () {
        _.extend(this.dateWidgetOptions, this.externalWidgetOptions);
        OroApp.DatagridFilterChoice.prototype.initialize.apply(this, arguments);
    },

    /**
     * Render filter criteria popup
     *
     * @param {Object} el
     * @return {*}
     * @protected
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate({
            name: this.name,
            choices: this.choices,
            inputClass: this.inputClass
        }));

        _.each(this.criteriaValueSelectors.value, function(actualSelector, name) {
            var visualSelector = this.criteriaValueSelectors.visualValue[name];
            this.dateWidgets[name] = this._initializeDateWidget(visualSelector, actualSelector);
        }, this);

        return this;
    },

    /**
     * Initialize date widget
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

        this.$(visualSelector).datepicker(options);
        var widget = this.$(visualSelector).datepicker('widget');
        widget.addClass(this.dateWidgetOptions.class);

        return widget;
    },

    /**
     * Handle click outside of criteria popup to hide it
     *
     * @param {Event} e
     * @protected
     */
    _onClickOutsideCriteria: function(e) {
        var elements = [this.$(this.criteriaSelector)];

        var widget = $(this.dateWidgetSelector);
        elements.push(widget);
        elements = _.union(elements, widget.find('span'));

        var clickedElement = _.find(elements, function(elem) {
            return _.isEqual(elem.get(0), e.target) || elem.has(e.target).length;
        });

        if (!clickedElement && $(e.target).prop('tagName') == 'BUTTON') {
            clickedElement = e.target;
        }

        if (!clickedElement) {
            this._hideCriteria();
            this._confirmValue(this._readCriteriaValue());
        }
    },

    /**
     * Get criteria hint value
     *
     * @return {String}
     * @protected
     */
    _getCriteriaHint: function() {
        if (this.confirmedValue.value) {
            var hint = '';
            var start = this._getInputValue(this.criteriaValueSelectors.visualValue.start);
            var end   = this._getInputValue(this.criteriaValueSelectors.visualValue.end);
            var type  = parseInt(this.confirmedValue.type);

            switch (type) {
                case this.typeValues.notBetween:
                    if (start && end) {
                        hint += this.choices[this.typeValues.notBetween] + ' ' + start + ' and ' + end
                    } else if (start) {
                        hint += ' before ' + start;
                    } else if (end) {
                        hint += ' after ' + end;
                    }
                    break;
                case this.typeValues.between:
                default:
                    if (start && end) {
                        hint += this.choices[this.typeValues.between] + ' ' + start + ' and ' + end
                    } else if (start) {
                        hint += ' from ' + start;
                    } else if (end) {
                        hint += ' to ' + end;
                    }
                    break;
            }
            if (hint) {
                return hint;
            }
        }

        return this.defaultCriteriaHint;
    },

    /**
     * Writes values from object into criteria elements
     *
     * @param {Object} value
     * @protected
     * @return {*}
     */
    _writeCriteriaValue: function(value) {
        this._setInputValue(this.criteriaValueSelectors.value.start, value.value.start);
        this._setInputValue(this.criteriaValueSelectors.value.end, value.value.end);
        this._setInputValue(this.criteriaValueSelectors.type, value.type);

        this._convertActualToVisual();

        return this;
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
            this.dateWidgetOptions.dateFormat
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
            this.dateWidgetOptions.altFormat
        );
    },

    /**
     * Convert data procedure
     *
     * @protected
     */
    _convertDateData: function(fromSelectors, toSelectors, fromFormat, toFormat) {
        _.each(fromSelectors, function(fromSelector, name) {
            var toSelector = toSelectors[name];
            var fromValue = this._getInputValue(fromSelector);
            var toValue = '';
            if (fromValue) {
                toValue = $.datepicker.formatDate(toFormat, $.datepicker.parseDate(fromFormat, fromValue));
            }
            this._setInputValue(toSelector, toValue);
        }, this);
    },

    /**
     * Reads value of criteria elements into object
     *
     * @return {Object}
     * @protected
     */
    _readCriteriaValue: function() {
        return {
            type: this._getInputValue(this.criteriaValueSelectors.type),
            value: {
                start: this._getInputValue(this.criteriaValueSelectors.value.start),
                end:   this._getInputValue(this.criteriaValueSelectors.value.end)
            }
        }
    },

    /**
     * Focus filter criteria input - no actions for date
     *
     * @protected
     */
    _focusCriteria: function() {
    },

    /**
     * Hide criteria popup
     *
     * @protected
     */
    _hideCriteria: function() {
        OroApp.DatagridFilterChoice.prototype._hideCriteria.apply(this, arguments);
        this._convertVisualToActual();
    },

    /**
     * Compare value with confirmed value, if it's differs than save new
     * confirmed value and trigger "changedData" event
     *
     * @param {Object} value
     * @protected
     */
    _confirmValue: function(value) {
        if (!this._looseObjectCompare(this.confirmedValue, value)) {
            var needUpdate = this.confirmedValue.value.start || this.confirmedValue.value.end
                || value.value.start || value.value.end;
            this.confirmedValue = _.clone(value);
            this._updateCriteriaHint();
            if (needUpdate) {
                this.trigger('update');
            }
        }
    }
});
