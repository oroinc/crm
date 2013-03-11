/**
 * Basic grid filter
 *
 * @class   OroApp.DatagridFilter
 * @extends Backbone.View
 */
OroApp.DatagridFilter = Backbone.View.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    className: 'btn-group filter-item',

    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <input type="text" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter" />' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    enabled: false,

    /** @property */
    name: 'input_name',

    /** @property */
    hint: 'Input Hint',

    /** @property */
    parameterSelectors: {
        value: 'input'
    },

    /** @property */
    events: {
        'change input': '_update',
        'click a.disable-filter': 'onClickDisable'
    },

    /**
     * Render filter template
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint
            })
        );
        return this;
    },

    /**
     * Filter data was updated
     *
     * @private
     */
    _update: function() {
        this.trigger('changedData');
    },

    /**
     * Handle click on filter disabler
     *
     * @param {Event} e
     */
    onClickDisable: function(e) {
        e.preventDefault();
        this.disable();
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
            if (this.hasValue()) {
                this.trigger('changedData');
            }
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
            if (this.hasValue()) {
                this.trigger('changedData');
            }
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
        this.$(this.parameterSelectors.value).val('');
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
        return this
    },

    /**
     * Check if filter contain value
     *
     * @return {Boolean}
     */
    hasValue: function() {
        return this.$(this.parameterSelectors.value).val() != '';
    },

    /**
     * Set filter parameters
     *
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        this.$(this.parameterSelectors.value).val(parameters['[value]']);
        return this;
    },

    /**
     * Get filter parameters
     *
     * @return {Object}
     */
    getParameters: function() {
        return {
            '[value]': this.$(this.parameterSelectors.value).val()
        };
    }
});
