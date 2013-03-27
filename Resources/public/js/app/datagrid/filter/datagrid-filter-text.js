/**
 * Text grid filter.
 *
 * Triggers events:
 *  - "disable" when filter is disabled
 *  - "update" when filter criteria is changed
 *
 * @class   OroApp.DatagridFilterText
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterText = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<button class="btn filter-criteria-selector oro-drop-opener oro-dropdown-toggle">' +
            '<%= label %>: <strong class="filter-criteria-hint"><%= criteriaHint %></strong>' +
            '<span class="caret"></span>' +
        '</button>' +
        '<a href="#" class="disable-filter"><i class="icon-remove hide-text">Close</i></a>' +
        '<div class="filter-criteria dropdown-menu" />'
    ),

    /**
     * Template for filter criteria
     *
     * @property
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div>' +
                '<input type="text" name="value" value=""/>' +
            '</div>' +
            '<div class="btn-group">' +
                '<button class="btn btn-mini filter-update">Update</button>' +
                '<button class="btn btn-mini filter-criteria-hide">Close</button>' +
            '</div>' +
        '</div>'
    ),

    /**
     * Selector to element of criteria hint
     *
     * @property {String}
     */
    criteriaHintSelector: '.filter-criteria-hint',

    /**
     * Selector to criteria popup container
     *
     * @property {String}
     */
    criteriaSelector: '.filter-criteria',

    /**
     * Selectors for filter criteria elements
     *
     * @property {Object}
     */
    criteriaValueSelectors: {
        value: 'input[name="value"]',
        nested: {
            end: 'input'
        }
    },

    /**
     * Value that was confirmed and processed.
     *
     * @property {Object}
     */
    confirmedValue: {},

    /**
     * Default value showed as filter's criteria hint
     *
     * @property {String}
     */
    defaultCriteriaHint: 'All',

    /**
     * View events
     *
     * @property {Object}
     */
    events: {
        'keyup input': '_onReadCriteriaInputKey',
        'click .filter-update': '_onClickUpdateCriteria',
        'click .filter-criteria-selector': '_onClickCriteriaSelector',
        'click .filter-criteria .filter-criteria-hide': '_onClickCloseCriteria',
        'click .disable-filter': '_onClickDisableFilter'
    },

    /**
     * Handle key press on criteria input elements
     *
     * @param {Event} e
     * @protected
     */
    _onReadCriteriaInputKey: function(e) {
        if (e.which == 13) {
            this._hideCriteria();
            this._confirmValue(this._readCriteriaValue());
        }
    },

    /**
     * Handle click on criteria update button
     *
     * @param {Event} e
     * @private
     */
    _onClickUpdateCriteria: function(e) {
        this._hideCriteria();
        this._confirmValue(this._readCriteriaValue());
    },

    /**
     * Handle click on criteria selector
     *
     * @param {Event} e
     * @protected
     */
    _onClickCriteriaSelector: function(e) {
        e.stopPropagation();
        $('body').trigger('click');
        this._showCriteria();
    },

    /**
     * Handle click on criteria close button
     *
     * @private
     */
    _onClickCloseCriteria: function() {
        this._hideCriteria();
        this.setValue(this.confirmedValue);
    },

    /**
     * Handle click on filter disabler
     *
     * @param {Event} e
     */
    _onClickDisableFilter: function(e) {
        e.preventDefault();
        this.disable();
    },

    /**
     * Handle click outside of criteria popup to hide it
     *
     * @param {Event} e
     * @protected
     */
    _onClickOutsideCriteria: function(e) {
        var elem = this.$('.filter-criteria');

        if (elem.get(0) !== e.target && !elem.has(e.target).length) {
            this._hideCriteria();
            this._confirmValue(this._readCriteriaValue());
        }
    },

    /**
     * Render filter view
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                label: this.label,
                criteriaHint: this._getCriteriaHint()
            })
        );

        this._renderCriteria(this.$('.filter-criteria'));
        this._clickOutsideCriteriaCallback = $.proxy(this._onClickOutsideCriteria, this);
        $('body').on('click', this._clickOutsideCriteriaCallback);
        this._initConfirmValue();

        return this;
    },

    /**
     * Set initial confirm value
     *
     * @protected
     */
    _initConfirmValue: function() {
        this.confirmedValue = this._readCriteriaValue();
    },

    /**
     * Render filter criteria popup
     *
     * @param {Object} el
     * @protected
     * @return {*}
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate());
        return this;
    },

    /**
     * Unsubscribe from click on body event
     *
     * @return {*}
     */
    remove: function() {
        $('body').off('click', this._clickOutsideCriteriaCallback);
        Backbone.View.prototype.remove.call(this);
        return this;
    },

    /**
     * Show criteria popup
     *
     * @private
     */
    _showCriteria: function() {
        this.$(this.criteriaSelector).show();
        this._focusCriteria();
    },

    /**
     * Hide criteria popup
     *
     * @private
     */
    _hideCriteria: function() {
        this.$(this.criteriaSelector).hide();
    },

    /**
     * Focus filter criteria input
     *
     * @protected
     */
    _focusCriteria: function() {
        this.$(this.criteriaSelector + ' input').focus().select();
    },

    /**
     * Reset filter elements
     *
     * @return {*}
     */
    reset: function() {
        this.setValue({
            value: ''
        });
        return this;
    },

    /**
     * Set value to filter's criteria and confirm it
     *
     * @param value
     * @return {*}
     */
    setValue: function(value) {
        this._writeCriteriaValue(value);
        this._confirmValue(value);
        return this;
    },

    /**
     * Get confirmed value of filter's criteria
     *
     * @return {Object}
     */
    getValue: function() {
        return this.confirmedValue;
    },

    /**
     * Compare value with confirmed value, if it's differs than save new
     * confirmed value and trigger "changedData" event
     *
     * @param {Object} value
     * @protected
     */
    _confirmValue: function(value) {
        var looseObjectCompare = function (obj1, obj2) {
            for (var i in obj1) {
                // both items are objects
                if (_.isObject(obj1[i]) && _.isObject(obj2[i]) && !looseObjectCompare(obj1[i], obj2[i])) {
                    return false;
                } else {
                    var equalsLoosely = (obj1[i] || '') == (obj2[i] || '');
                    var eitherNumber = _.isNumber(obj1[i]) || _.isNumber(obj2[i]);
                    var equalsNumbers = Number(obj1[i]) == Number(obj2[i]);
                    if (!(equalsLoosely || (eitherNumber && equalsNumbers))) {
                        return false;
                    }
                }
            }
            return true;
        };

        if (!looseObjectCompare(this.confirmedValue, value)) {
            this.confirmedValue = _.clone(value);
            this._updateCriteriaHint();
            this.trigger('update');
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
            value: this._getInputValue(this.criteriaValueSelectors.value)
        }
    },

    /**
     * Updates criteria hint element with actual criteria hint value
     *
     * @private
     * @return {*}
     */
    _updateCriteriaHint: function() {
        this.$(this.criteriaHintSelector).html(this._getCriteriaHint());
        return this;
    },

    /**
     * Get criteria hint value
     *
     * @return {String}
     * @protected
     */
    _getCriteriaHint: function() {
        return this.confirmedValue.value ? '"' + this.confirmedValue.value + '"': this.defaultCriteriaHint;
    },

    /**
     * Gets input value. Radio inputs are supported.
     *
     * @param {String|Object} input
     * @return {*}
     * @protected
     */
    _getInputValue: function(input) {
        var result = undefined;
        var $input = this.$(input);
        switch ($input.attr('type')) {
            case 'radio':
                $input.each(function() {
                    if ($(this).is(':checked')) {
                        result = $(this).val();
                    }
                });
                break;
            default:
                result = $input.val();

        }
        return result;
    },

    /**
     * Sets input value. Radio inputs are supported.
     *
     * @param {String|Object} input
     * @param {String} value
     * @protected
     * @return {*}
     */
    _setInputValue: function(input, value) {
        var $input = this.$(input);
        switch ($input.attr('type')) {
            case 'radio':
                $input.each(function() {
                    var $input = $(this);
                    if ($input.attr('value') == value) {
                        $input.attr('checked', 'checked');
                    } else {
                        $(this).removeAttr('checked');
                    }
                });
                break;
            default:
                $input.val(value);

        }
        return this;
    },

    /**
     * Set filter parameters
     *
     * @deprecated
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        var value = this._transformParametersToValue(parameters);
        this._writeCriteriaValue(value);
        this._confirmValue(value);
        return this;
    },

    /**
     * Get filter parameters
     *
     * @deprecated
     * @return {Object}
     */
    getParameters: function() {
        var value = this.getValue();
        return this._transformValueToParameters(value);
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
            value: parameters['[value]']
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
            '[value]': value.value
        }
    }
});
