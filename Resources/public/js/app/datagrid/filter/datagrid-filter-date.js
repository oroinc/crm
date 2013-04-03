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
                'from <input type="text" name="start" value="" size="10" maxlength="10" style="width:100px;" /> ' +
                'to <input type="text" name="end" value="" size="10" maxlength="10" style="width:100px;" /> ' +
            '</div>' +
            '<div>' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<input type="radio" name="<%= name %>" value="<%= value %>" />&nbsp;<%= hint %><br/>' +
                '<% }); %>' +
                '<br/>' +
            '</div>' +
            '<div class="btn-group">' +
                '<button class="btn btn-mini filter-update">Update</button>' +
                '<button class="btn btn-mini filter-criteria-hide">Close</button>' +
            '</div>' +
        '</div>'
    ),

    /**
     * Selectors for filter data
     *
     * @property
     */
    criteriaValueSelectors: {
        type: 'input[type="radio"]:checked',
        value: {
            start: 'input[name="start"]',
            end:   'input[name="end"]'
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
     * Date widget options
     *
     * @property
     */
    dateWidgetOptions: {
        changeMonth: true,
        changeYear:  true,
        yearRange:  '-50:+1',
        dateFormat: 'yy-mm-dd',
        class:      'date-filter-widget dropdown-menu'
    },

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
     * Render filter criteria popup
     *
     * @param {Object} el
     * @return {*}
     * @protected
     */
    _renderCriteria: function(el) {
        OroApp.DatagridFilterChoice.prototype._renderCriteria.apply(this, arguments);

        _.each(this.criteriaValueSelectors.value, function(selector, name) {
            this.$(selector).datepicker(this.dateWidgetOptions);
            this.dateWidgets[name] = this.$(selector).datepicker('widget');
            this.dateWidgets[name].addClass(this.dateWidgetOptions.class);
        }, this);

        return this;
    },

    /**
     * Handle click outside of criteria popup to hide it
     *
     * @param {Event} e
     * @protected
     */
    _onClickOutsideCriteria: function(e) {
        var elements = [this.$(this.criteriaSelector)];

        _.each(this.dateWidgets, function(widget) {
            elements.push(widget);
            elements = _.union(elements, widget.find('span'));
        });

        var clickedElement = _.find(elements, function(elem) {
            return _.isEqual(elem.get(0), e.target) || elem.has(e.target).length;
        });

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
        // if (!this.confirmedValue.value) {
            return this.defaultCriteriaHint;
//        } else if (_.has(this.choices, this.confirmedValue.type)) {
//            return this.choices[this.confirmedValue.type] + ' "' + this.confirmedValue.value + '"'
//        } else {
//            return '"' + this.confirmedValue.value + '"';
//        }
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
        return this;
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
