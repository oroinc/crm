/**
 * Choice filter: filter type as option + filter value as string
 *
 * @class   OroApp.DatagridFilterChoice
 * @extends OroApp.DatagridFilterText
 */
OroApp.DatagridFilterChoice = OroApp.DatagridFilterText.extend({
    /**
     * Template for filter criteria
     *
     * @property {function(Object, ?Object=): String}
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div>' +
                '<input type="text" name="value" value=""/>' +
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
     * Selectors for filter criteria elements
     *
     * @property {Object}
     */
    criteriaValueSelectors: {
        value: 'input[name="value"]',
        type: 'input[type="radio"]'
    },

    /** @property */
    choices: {},

    /**
     * Empty value object
     *
     * @property {Object}
     */
    emptyValue: {
        value: '',
        type: ''
    },

    /**
     * Render filter criteria popup
     *
     * @param {Object} el
     * @protected
     * @return {*}
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate({
            name: this.name,
            choices: this.choices
        }));
        return this;
    },

    /**
     * Get criteria hint value
     *
     * @return {String}
     * @protected
     */
    _getCriteriaHint: function() {
        if (!this.confirmedValue.value) {
            return this.defaultCriteriaHint;
        } else if (_.has(this.choices, this.confirmedValue.type)) {
            return this.choices[this.confirmedValue.type] + ' "' + this.confirmedValue.value + '"'
        } else {
            return '"' + this.confirmedValue.value + '"';
        }
    },

    /**
     * Writes values from object into criteria elements
     *
     * @param {Object} value
     * @protected
     * @return {*}
     */
    _writeCriteriaValue: function(value) {
        this._setInputValue(this.criteriaValueSelectors.value, value.value);
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
            value: this._getInputValue(this.criteriaValueSelectors.value),
            type: this._getInputValue(this.criteriaValueSelectors.type)
        }
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
            var needUpdate = this.confirmedValue.value || value.value;
            this.confirmedValue = _.clone(value);
            this._updateCriteriaHint();
            if (needUpdate) {
                this.trigger('update');
            }
        }
    }
});

