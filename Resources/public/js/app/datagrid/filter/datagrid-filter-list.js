/**
 * View that represents all grid filters
 *
 * @class   OroApp.DatagridFilterList
 * @extends Backbone.View
 */
OroApp.DatagridFilterList = Backbone.View.extend({
    /**
     * List of filter objects
     *
     * @property
     */
    filters: {},

    /**
     * Filter list template
     *
     * @property
     */
    addButtonTemplate: _.template(
        '<select id="add-filter-select" multiple>' +
            '<% _.each(filters, function (filter, name) { %>' +
                '<option value="<%= name %>" <% if (filter.enabled) { %>selected<% } %>>' +
                    '<%= filter.label %>' +
                '</option>' +
            '<% }); %>' +
        '</select>'
    ),

    /**
     * Filter list input selector
     *
     * @property
     */
    filterSelector: '#add-filter-select',

    /**
     * Add filter button hint
     *
     * @property
     */
    addButtonHint: '+ Add filter',

    /** @property */
    events: {
        'change #add-filter-select': '_onChangeFilterSelect'
    },

    /**
     * Select widget object
     *
     * @property {OroApp.MultiSelectDecorator}
     */
    selectWidget: null,

    /**
     * Widget button selector
     *
     * @property
     */
    buttonSelector: '.ui-multiselect.filter-list',

    /**
     * Initialize filter list options
     *
     * @param {Object} options
     * @param {OroApp.PageableCollection} [options.collection]
     * @param {Object} [options.filters]
     * @param {String} [options.addButtonHint]
     */
    initialize: function(options)
    {
        this.collection = options.collection;

        if (options.filters) {
            this.filters = options.filters;
        }

        if (options.addButtonHint) {
            this.addButtonHint = options.addButtonHint;
        }

        _.each(this.filters, function(filter, name) {
            this.filters[name] = new filter();
            this.listenTo(this.filters[name], "update", this._onFilterUpdated);
            this.listenTo(this.filters[name], "disable", this._onFilterDisabled);
        }, this);

        this.collection.on('beforeFetch', this._beforeCollectionFetch, this);
        this.collection.on('updateState', this._onUpdateCollectionState, this);
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Triggers when filter is updated
     *
     * @param {OroApp.DatagridFilter} filter
     * @protected
     */
    _onFilterUpdated: function(filter) {
        if (this.ignoreFiltersUpdateEvents) {
            return;
        }
        this.collection.state.currentPage = 1;
        this.collection.fetch();
    },

    /**
     * Triggers when filter is disabled
     *
     * @param {OroApp.DatagridFilter} filter
     * @protected
     */
    _onFilterDisabled: function(filter) {
        this.disableFilter(filter);
    },

    /**
     * Triggers before collection fetch it's data
     *
     * @protected
     */
    _beforeCollectionFetch: function(collection) {
        collection.state.filters = this._createState();
    },

    /**
     * Triggers when collection state is updated
     *
     * @param {OroApp.PageableCollection} collection
     */
    _onUpdateCollectionState: function(collection) {
        this.ignoreFiltersUpdateEvents = true;
        this._applyState(collection.state.filters || {});
        this.ignoreFiltersUpdateEvents = false;
    },

    /**
     * Create state according to filters parameters
     *
     * @return {Object}
     * @protected
     */
    _createState: function() {
        var state = {};
        _.each(this.filters, function(filter, name) {
            var shortName = '__' + name;
            if (filter.enabled) {
                if (!filter.isEmpty()) {
                    state[name] = filter.getValue();
                } else if (!filter.defaultEnabled) {
                    state[shortName] = 1;
                }
            } else if (filter.defaultEnabled) {
                state[shortName] = 0;
            }
        }, this);

        return state;
    },

    /**
     * Apply filter values from state
     *
     * @param {Object} state
     * @protected
     * @return {*}
     */
    _applyState: function(state) {
        _.each(this.filters, function(filter, name) {
            var shortName = '__' + name;
            if (_.has(state, name)) {
                var filterState = state[name];
                if (!_.isObject(filterState)) {
                    filterState = {
                        value: filterState
                    }
                }
                this.enableFilter(filter.setValue(filterState));
            } else if (_.has(state, shortName)) {
                if (Number(state[shortName])) {
                    this.enableFilter(filter.reset());
                } else {
                    this.disableFilter(filter.reset());
                }
            }
        }, this);

        return this;
    },

    /**
     * Triggers when filter select is changed
     *
     * @protected
     */
    _onChangeFilterSelect: function() {
        this._processFilterStatus();
    },

    /**
     * Enable filter
     *
     * @param {OroApp.DatagridFilter} filter
     * @return {*}
     */
    enableFilter: function(filter) {
        filter.enable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).attr('selected', 'selected');
        this.selectWidget.multiselect('refresh');
        return this;
    },

    /**
     * Disable filter
     *
     * @param {OroApp.DatagridFilter} filter
     * @return {*}
     */
    disableFilter : function(filter) {
        filter.disable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).removeAttr('selected');
        this.selectWidget.multiselect('refresh');
        return this;
    },

    /**
     * Render filter list
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        _.each(this.filters, function(filter) {
            filter.render();
            if (!filter.enabled) {
                filter.hide();
            }
            this.$el.append(filter.$el);
        }, this);

        this.$el.prepend(this.addButtonTemplate({
            filters: this.filters
        }));

        this._initializeSelectWidget();

        this.trigger("rendered");

        if (_.isEmpty(this.filters)) {
            this.$el.hide();
        }

        return this;
    },

    /**
     * Initialize multiselect widget
     *
     * @protected
     */
    _initializeSelectWidget: function() {
        this.selectWidget = new OroApp.MultiSelectDecorator(this.$(this.filterSelector), {
            selectedList: 0,
            selectedText: this.addButtonHint,
            classes: 'filter-list select-filter-widget',
            open: $.proxy(function() {
                this.selectWidget.onOpenDropdown();
                this._setDropdownWidth();
                this._updateDropdownPosition();
            }, this)
        });

        this.selectWidget.setViewDesign(this);
        this.$('.filter-list span:first').replaceWith('<a id="add-filter-button" href="#">' + this.addButtonHint +'</a>');
    },

    /**
     * Set design for select dropdown
     *
     * @protected
     */
    _setDropdownWidth: function() {
        var widget = this.selectWidget.getWidget();
        var requiredWidth = this.selectWidget.getMinimumDropdownWidth() + 24;
        widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
        widget.find('input[type="search"]').width(requiredWidth - 22);
    },

    /**
     * Activate/deactivate all filter depends on its status
     *
     * @protected
     */
    _processFilterStatus: function() {
        var activeFilters = this.$(this.filterSelector).val();

        _.each(this.filters, function(filter, name) {
            if (!filter.enabled && _.indexOf(activeFilters, name) != -1) {
                this.enableFilter(filter);
            } else if (filter.enabled && _.indexOf(activeFilters, name) == -1) {
                this.disableFilter(filter);
            }
        }, this);

        this._updateDropdownPosition();
    },

    /**
     * Set dropdown position according to current element
     *
     * @protected
     */
    _updateDropdownPosition: function() {
        var button = this.$(this.buttonSelector);
        var buttonPosition = button.offset();
        var widgetWidth = this.selectWidget.getWidget().outerWidth();
        var windowWidth = $(window).width();
        var widgetLeftOffset = buttonPosition.left;
        if (buttonPosition.left + widgetWidth > windowWidth) {
            widgetLeftOffset = buttonPosition.left + button.outerWidth() - widgetWidth;
        }

        this.selectWidget.getWidget().css({
            top: buttonPosition.top + button.outerHeight(),
            left: widgetLeftOffset
        });
    }
});
