OroApp = OroApp || {};
OroApp.Filter = OroApp.Filter || {};

/**
 * Choice filter: filter type as option + filter value as string
 *
 * @class   OroApp.Filter.ChoiceFilter
 * @extends OroApp.Filter.TextFilter
 */
OroApp.Filter.ChoiceFilter = OroApp.Filter.TextFilter.extend({
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
            '<div class="horizontal">' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<div class="oro-clearfix">' +
                        '<input type="radio" id="<%= name %>-<%= value %>" name="<%= name %>" value="<%= value %>" /><label for ="<%= name %>-<%= value %>"><%= hint %></label>' +
                    '</div>'+
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
     * @inheritDoc
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate({
            name: this.name,
            choices: this.choices
        }));
        return this;
    },

    /**
     * @inheritDoc
     */
    _getCriteriaHint: function() {
        var value = this._getDisplayValue();
        if (!value.value) {
            return this.defaultCriteriaHint;
        } else if (_.has(this.choices, value.type)) {
            return this.choices[value.type] + ' "' + value.value + '"'
        } else {
            return '"' + value.value + '"';
        }
    },

    /**
     * @inheritDoc
     */
    _writeDOMValue: function(value) {
        this._setInputValue(this.criteriaValueSelectors.value, value.value);
        this._setInputValue(this.criteriaValueSelectors.type, value.type);
        return this;
    },


    /**
     * @inheritDoc
     */
    _readDOMValue: function() {
        return {
            value: this._getInputValue(this.criteriaValueSelectors.value),
            type: this._getInputValue(this.criteriaValueSelectors.type)
        }
    },

    /**
     * @inheritDoc
     */
    _triggerUpdate: function(newValue, oldValue) {
        if (newValue.value || oldValue.value) {
            this.trigger('update');
        }
    }
});

