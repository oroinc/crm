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
                'From <input type="text" name="start" value="" size="10" maxlength="10" style="width:100px;" /> ' +
                'to <input type="text" name="end" value="" size="10" maxlength="10" style="width:100px;" />' +
            '</div>' +
            '<div>' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<input type="radio" name="<%= name %>" value="<%= value %>" />&nbsp;<%= hint %><br/>' +
                '<% }); %>' +
                '<br/>' +
            '</div>' +
            '<div class="oro-action">' +
                '<div class="btn-group">' +
                    '<button class="btn btn-mini filter-criteria-hide">Close</button>' +
                    '<button class="btn btn-mini filter-update">Update</button>' +
                '</div>' +
            '</div>' +
        '</div>'
    ),

    /** @property */
    criteriaValueSelectors: {
        type: 'input[type="radio"]:checked',
        value: {
            start: 'input[name="start"]',
            end:   'input[name="end"]'
        }
    },

    /** @property */
    emptyValue: {
        type: '',
        value: {
            start: '',
            end: ''
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
        this._setInputValue(this.criteriaValueSelectors.type, value.type);
        this._setInputValue(this.criteriaValueSelectors.value.start, value.value.start);
        this._setInputValue(this.criteriaValueSelectors.value.end, value.value.end);
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
     * Focus filter criteria input
     *
     * @protected
     */
    _focusCriteria: function() {
        this.$(this.criteriaValueSelectors.value.start).focus().select();
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
