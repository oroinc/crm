OroApp = OroApp || {};
OroApp.Filter = OroApp.Filter || {};

/**
 * Multiselect decorator class.
 * Wraps multiselect widget and provides design modifications
 *
 * @class OroApp.Filter.MultiSelectDecorator
 */
OroApp.Filter.MultiSelectDecorator = function (element, parameters) {
    this.element = element;
    _.extend(this.parameters, parameters);
    this.initialize();
};

_.extend(OroApp.Filter.MultiSelectDecorator.prototype, {
    /**
     * Multiselect widget element container
     *
     * @property
     */
    element: null,

    /**
     * Default multiselect widget parameters
     *
     * @property
     */
    parameters: {
        height: 'auto'
    },

    /**
     * Initialize all required properties
     */
    initialize: function() {
        // initialize multiselect widget
        this.multiselect(this.parameters);

        // initialize multiselect filter
        this.multiselectfilter({
            label: '',
            placeholder: '',
            autoReset: true
        });
    },

    /**
     * Set design for view
     *
     * @param {Backbone.View} view
     */
    setViewDesign: function(view) {
        view.$('.ui-multiselect').removeClass('ui-widget').removeClass('ui-state-default');
        view.$('.ui-multiselect span.ui-icon').remove();
    },

    /**
     * Fix dropdown design
     *
     * @protected
     */
    _setDropdownDesign: function() {
        var widget = this.getWidget();
        widget.addClass('dropdown-menu');
        widget.removeClass('ui-widget-content');
        widget.removeClass('ui-widget');
        widget.find('.ui-widget-header').removeClass('ui-widget-header');
        widget.find('.ui-multiselect-filter').removeClass('ui-multiselect-filter');
        widget.find('ul li label').removeClass('ui-corner-all');
    },

    /**
     * Action performed on dropdown open
     */
    onOpenDropdown: function() {
        this._setDropdownDesign();
        this.getWidget().find('input[type="search"]').focus();
        $('body').trigger('click');
    },

    /**
     * Get minimum width of dropdown menu
     *
     * @return {Number}
     */
    getMinimumDropdownWidth: function() {
        var minimumWidth = 0;
        var elements = this.getWidget().find('.ui-multiselect-checkboxes li');
        _.each(elements, function(element) {
            var width = this._getTextWidth($(element).find('label'));
            if (width > minimumWidth) {
                minimumWidth = width;
            }
        }, this);

        return minimumWidth;
    },

    /**
     * Get element width
     *
     * @param {Object} element
     * @return {Integer}
     * @protected
     */
    _getTextWidth: function(element) {
        var html_org = element.html();
        var html_calc = '<span>' + html_org + '</span>';
        element.html(html_calc);
        var width = element.find('span:first').width();
        element.html(html_org);
        return width;
    },

    /**
     * Get multiselect widget
     *
     * @return {Object}
     */
    getWidget: function() {
        return this.multiselect('widget');
    },

    /**
     * Proxy for multiselect method
     *
     * @param functionName
     * @return {Object}
     */
    multiselect: function(functionName) {
        return this.element.multiselect(functionName);
    },

    /**
     * Proxy for multiselectfilter method
     *
     * @param functionName
     * @return {Object}
     */
    multiselectfilter: function(functionName) {
        return this.element.multiselectfilter(functionName);
    },

    /**
     *  Set dropdown position according to button element
     *
     * @param {Object} button
     */
    updateDropdownPosition: function(button) {
        var position = button.offset();
        this.getWidget().css({
            top: position.top + button.outerHeight(),
            left: position.left
        });
    }
});

