/**
 * Text grid filter.
 *
 * Triggers events:
 *  - "disabled" when filter is disabled
 *  - "changedData" when filter criteria is changed
 *
 * @class   OroApp.DatagridFilterText
 * @extends Backbone.View
 */
OroApp.DatagridFilterText = Backbone.View.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    className: 'btn-group filter-item',

    /** @property */
    template: _.template(
        '<button class="btn filter-criteria-selector">' +
            '<%= label %>: <strong class="filter-criteria-hint"><%= criteriaHint %></strong>' +
            '<span class="caret"></span>' +
        '</button>' +
        '<a href="#" class="disable-filter" />' +
        '<div class="filter-criteria" />'
    ),

    /**
     * Template for
     *
     * @property
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div>' +
                '<input type="text" value=""/>' +
            '</div>' +
            '<div class="btn-group">' +
                '<button href="#" class="btn btn-mini filter-update">Update</button>' +
                '<button href="#" class="btn btn-mini filter-criteria-hide">Close</button>' +
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
     * Actual value of filter's all inputs
     *
     * @property {Object}
     */
    actualValue: {},

    /**
     * Value that was confirmed and processed.
     *
     * @property {Object}
     */
    confirmedValue: {},

    /**
     * Is filter enabled
     *
     * @property {Boolean}
     */
    enabled: false,

    /**
     * Name of filter field
     *
     * @property {String}
     */
    name: 'input_name',

    /**
     * Label of filter
     *
     * @property {String}
     */
    label: 'Input Label',

    /**
     * Default value showed as filter's criteria hint
     *
     * @property {String}
     */
    defaultCriteriaHint: 'All',

    /**
     * Selectors for filter criteria elements
     *
     * @property {Object}
     */
    criteriaSelectors: {
        value: 'input'
    },

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
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;

        if (key == 13) {
            this._hideCriteria();
            this._confirmValue();
        } else {
            this._readActualValue();
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
        this._confirmValue();
    },

    /**
     * Handle click on criteria selector
     *
     * @param {Event} e
     * @protected
     */
    _onClickCriteriaSelector: function(e) {
        e.stopPropagation();
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

        if ( elem.get(0) !== event.target && !elem.has(event.target).length ) {
            this._hideCriteria();
            this._confirmValue();
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

        this._readActualValue();
        this.confirmedValue = _.clone(this.actualValue);

        return this;
    },

    /**
     * Render filter criteria popup
     *
     * @param {Object} el
     * @protected
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate());
        return this.popupCriteriaTemplate();
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
     * Show popup
     *
     * @private
     */
    _showCriteria: function() {
        this.$(this.criteriaSelector).show();
        this._focusCriteria();
    },

    /**
     * Hide popup
     *
     * @private
     */
    _hideCriteria: function() {
        this.$(this.criteriaSelector).hide();
    },

    /**
     * Focus filter criteria's input
     *
     * @protected
     */
    _focusCriteria: function() {
        this.$(this.criteriaSelector + ' input').focus().select();
    },

    /**
     * Enable filter
     *
     * @return {*}
     */
    enable: function() {
        if (!this.enabled) {
            this.enabled = true;
            this.show();
        }
        return this;
    },

    /**
     * Disable filter
     *
     * @return {*}
     */
    disable: function() {
        if (this.enabled) {
            this.enabled = false;
            this.hide();
            this.trigger('disabled', this);
            this.reset();
        }
        return this;
    },

    /**
     * Reset filter form elements
     *
     * @return {*}
     */
    reset: function() {
        this.$(this.criteriaSelectors.value).val('');
        this._confirmValue();
        return this;
    },

    /**
     * Show filter
     *
     * @return {*}
     */
    show: function() {
        this.$el.css('display', 'inline-block');
        return this;
    },

    /**
     * Hide filter
     *
     * @return {*}
     */
    hide: function() {
        this.$el.css('display', 'none');
        return this;
    },

    /**
     * Check if filter contain value
     *
     * @return {Boolean}
     */
    hasValue: function() {
        return this.confirmedValue.value != '';
    },

    /**
     * Set value to filter's criteria
     *
     * @param value
     */
    setValue: function(value) {
        this.$(this.criteriaSelectors.value).val(value.value);
        this._confirmValue();
    },

    /**
     * Get value of filter's criteria
     *
     * @return {Object}
     */
    getValue: function() {
        return this.confirmedValue;
    },

    /**
     * Read filter criteria value
     *
     * @return {Object}
     * @protected
     */
    _readActualValue: function() {
        this.actualValue = {
            value: this.$(this.criteriaSelectors.value).val()
        };
    },

    /**
     * Reads actual value from criteria inputs, compare value with confirmed value, if it's differs than save new
     * confirmed value and trigger "changedData" event
     *
     * @protected
     */
    _confirmValue: function() {
        this._readActualValue();
        if (!_.isEqual(this.confirmedValue, this.actualValue)) {
            this.confirmedValue = _.clone(this.actualValue);
            this._updateCriteriaHint();
            // TODO Rename event?
            this.trigger('changedData');
        }
    },

    /**
     * Updates criteria hint element with actual criteria hint value
     *
     * @private
     */
    _updateCriteriaHint: function() {
        this.$(this.criteriaHintSelector).html(this._getCriteriaHint());
    },

    /**
     * Get criteria hint value
     *
     * @return {String}
     * @protected
     */
    _getCriteriaHint: function() {
        return this.confirmedValue.value ? this.confirmedValue.value : this.defaultCriteriaHint;
    },

    /**
     * Set filter parameters
     *
     * @deprecated
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        this.$(this.criteriaSelectors.value).val(parameters['[value]']);
        this._confirmValue();
        return this;
    },

    /**
     * Get filter parameters
     *
     * @deprecated
     * @return {Object}
     */
    getParameters: function() {
        return {
            '[value]': this.confirmedValue.value
        };
    }
});
