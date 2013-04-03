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
                    '<input type="radio" name="type" value="<%= value %>" />&nbsp;<%= hint %><br/>' +
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
        type: 'input[name="type"]'
    },

    /** @property */
    /*events: {
        'change input[name="type"]': '_updateOnType',
        'change input[name="value"]': '_update',
        'click a.disable-filter': 'onClickDisable'
    },

    /** @property */
    choices: {},

    /**
     * Render filter criteria popup
     *
     * @param {Object} el
     * @protected
     * @return {*}
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate({
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
     * Reset filter elements
     *
     * @return {*}
     */
    reset: function() {
        this.setValue({
            value: '',
            type: ''
        });
        return this;
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
     * Transforms parameters to value
     *
     * @deprecated
     * @param {Object} parameters
     * @return {Object}
     * @protected
     */
    _transformParametersToValue: function(parameters) {
        return {
            value: parameters['[value]'],
            type: parameters['[type]']
        }
    },

    /**
     * Transforms value to parameters
     *
     * @deprecated
     * @param {Object} value
     * @return {Object}
     * @protected
     */
    _transformValueToParameters: function(value) {
        return {
            '[value]': value.value,
            '[type]': value.type
        }
    }
});

