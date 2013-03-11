/**
 * Choice filter: filter type as option + filter value as string
 *
 * @class   OroApp.DatagridFilterChoice
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterChoice = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>:' +
            '<% _.each(choices, function (hint, value) { %>' +
            '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
            '<% }); %>' +
            '<input type="text" name="value" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter" />' +
            '<span class="caret"></span>' +
            '</div>'
    ),

    /** @property */
    parameterSelectors: {
        type:  'input[name="type"]:checked',
        value: 'input[name="value"]'
    },

    /** @property */
    events: {
        'change input[name="type"]': '_updateOnType',
        'change input[name="value"]': '_update',
        'click a.disable-filter': 'onClickDisable'
    },

    /** @property */
    choices: {},

    /**
     * Render filter template
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint,
                choices: this.choices
            })
        );
        return this;
    },

    /**
     * Update grid data when filter type is changed
     *
     * @private
     */
    _updateOnType: function() {
        if (this.hasValue()) {
            this.trigger('changedData');
        }
    },

    /**
     * Reset filter form elements
     *
     * @return {*}
     */
    reset: function() {
        this.$(this.parameterSelectors.type).val('');
        this.$(this.parameterSelectors.value).val('');
        return this;
    },

    /**
     * Set filter parameters
     *
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        this.$(this.parameterSelectors.type).val(parameters['[type]']);
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
            '[type]':  this.$(this.parameterSelectors.type).val(),
            '[value]': this.$(this.parameterSelectors.value).val()
        };
    }
});

