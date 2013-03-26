/**
 * Select filter: filter value as select option
 *
 * @class   OroApp.DatagridFilterSelect
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterSelect = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn filter-select">' +
            '<%= label %>: ' +
            '<select>' +
                '<option value=""><%= placeholder %></option>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
        '</div>' +
        '<a href="#" class="disable-filter"><i class="icon-remove hide-text">Close</i></a>'
    ),

    /** @property */
    options: {},

    /** @property */
    placeholder: 'All',

    /** @property */
    select2Element: 'select',

    /** @property */
    select2Config: {
        width: 'off',
        dropdownCssClass: 'select-filter-dropdown'
    },

    /** @property */
    events: {
        'click .filter-select': '_onClickFilterSelect',
        'click .disable-filter': '_onClickDisableFilter',
        'change select': '_onSelectChange'
    },

    /** @property */
    needOpenDropdown: true,

    /**
     * Render filter template
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                label: this.label,
                options: this.options,
                placeholder: this.placeholder
            })
        );

        this._initSelect2();

        return this;
    },

    /**
     * Create Select2 instance
     */
    _initSelect2: function() {
        // create select2 instance
        var select2Object = this.$(this.select2Element).select2(this.select2Config);

        var data = {
            filterElement: this.$el,
            select2Config: this.select2Config
        };
        select2Object.on('open', data, this._onOpenDropdown);
    },

    /**
     * Triggers change data event
     *
     * @protected
     */
    _onSelectChange: function() {
        this.trigger('changedData');
        this.needOpenDropdown = false;
    },

    /**
     * Open option dropdown in case of click on non-select area
     *
     * @protected
     */
    _onClickFilterSelect: function() {
        if (this.needOpenDropdown) {
            this.$(this.select2Element).select2('open');
        }
        this.needOpenDropdown = true;
    },

    /**
     * Recalculate position of the select filter drop down relative to filter container,
     * trigger click on body to process filters hiding
     *
     * @param event
     */
    _onOpenDropdown: function(event) {
        var filterElement = event.data.filterElement,
            dropdown = $('.' + event.data.select2Config.dropdownCssClass),
            body = filterElement.closest('body'),
            offset = filterElement.offset(),
            dropLeft = offset.left,
            dropWidth = dropdown.outerWidth(false),
            viewPortRight = $(window).scrollLeft() + $(window).width(),
            enoughRoomOnRight = dropLeft + dropWidth <= viewPortRight;


        if (body.css('position') !== 'static') {
            var bodyOffset = body.offset();
            dropLeft -= bodyOffset.left;
        }

        if (!enoughRoomOnRight) {
            dropLeft = offset.left + width - dropWidth;
        }

        dropdown.css('left', dropLeft);

        $('body').trigger('click');
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
     * Set value to filter's criteria and confirm it
     *
     * @param value
     * @return {*}
     */
    setValue: function(value) {
        this.$(this.select2Element).select2('val', value.value);
        return this;
    },

    /**
     * Get confirmed value of filter's criteria
     *
     * @return {Object}
     */
    getValue: function() {
        return {
            value: this.$(this.select2Element).select2('val')
        };
    },

    /**
     * Set filter parameters
     *
     * @deprecated
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        var value = {
            value: parameters['[value]']
        };
        this.setValue(value);
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
        return {
            '[value]': value.value
        };
    }
});
