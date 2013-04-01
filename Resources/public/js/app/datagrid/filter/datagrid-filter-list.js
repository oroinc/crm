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
    addButtonHint: 'Add filter',

    /** @property */
    events: {
        'change #add-filter-select': '_processFilterStatus'
    },

    /**
     * Flag that allows temporary disable reloading of collection
     *
     * @property
     */
    needReloadCollection: true,

    /**
     * Select widget object
     *
     * @property
     */
    selectWidget: null,

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
            this.listenTo(this.filters[name], "update", this._reloadCollection);
            this.listenTo(this.filters[name], "disable", this.disableFilter);
        }, this);

        this._saveState();
        this.collection.on('reset', this._restoreState, this);
        this.collection.on('updateState', this.updateState, this);

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Save filter state
     *
     * @protected
     */
    _saveState: function() {
        this.collection.state.filters = this._createState();
        this.collection.state.filtersParams = this.getAllParameters();
    },

    /**
     * Updates collection state
     *
     * @param {OroApp.PageableCollection} collection
     * @param {Object} options
     */
    updateState: function(collection, options) {
        var storedFlag = this.needReloadCollection;
        if (_.has(options, 'needReloadCollection')) {
            this.needReloadCollection = options.needReloadCollection;
        }

        this._restoreState(collection, options);
        this._saveState();

        if (options.hasOwnProperty('needReloadCollection')) {
            this.needReloadCollection = storedFlag;
        }
    },

    /**
     * Restore filter state from collection
     *
     * @param {OroApp.PageableCollection} collection
     * @param {Object} options
     * @protected
     */
    _restoreState: function(collection, options) {
        if (options.ignoreUpdateFilters) {
            return;
        }
        this._applyState(collection.state.filters ? collection.state.filters : {});
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
     * Fix dropdown position
     *
     * @protected
     */
    _updateDropdownPosition: function() {
        var button = this.$('.ui-multiselect.filter-list');
        var position = button.offset();
        this.selectWidget.getWidget().css({
            top: position.top + button.outerHeight(),
            left: position.left
        });
    },

    /**
     * Enable filter
     *
     * @param {OroApp.DatagridFilter} filter
     */
    enableFilter: function(filter) {
        filter.enable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).attr('selected', 'selected');
        this.selectWidget.multiselect('refresh');
    },

    /**
     * Disable filter
     *
     * @param {OroApp.DatagridFilter} filter
     */
    disableFilter : function(filter) {
        filter.disable();
        this._saveState();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).removeAttr('selected');
        this.selectWidget.multiselect('refresh');
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

        this.$el.append(this.addButtonTemplate({
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
     * Reload collection data with current filters
     *
     * @private
     * @return {*}
     */
    _reloadCollection: function() {
        this._saveState();
        this.collection.state.currentPage = 1;
        if (this.needReloadCollection) {
            this.collection.fetch({
                ignoreUpdateFilters: true
            });
        }
        return this;
    },

    /**
     * Create state according to filters parameters
     *
     * @return {Object}
     * @private
     * @return {*}
     */
    _createState: function() {
        var state = {};
        _.each(this.filters, function(filter, name) {
            if (filter.enabled) {
                var filterParameters = filter.getParameters();
                var value = {};
                _.each(_.keys(filterParameters), function(key) {
                    if (filterParameters[key]) {
                        value[key] = filterParameters[key];
                    }
                });
                var valueKeys = _.keys(value);
                if (valueKeys.length == 1 && valueKeys[0] == '[value]') {
                    state[name] = value['[value]'];
                } else if (valueKeys.length) {
                    state[name] = value;
                } else {
                    state['__' + name] = 1;
                }
            }
        }, this);

        return state;
    },

    /**
     * Get parameters of all filters
     *
     * @return {Object}
     */
    getAllParameters: function() {
        var result = {};
        _.each(this.filters, function(filter, name) {
            if (filter.enabled) {
                var parameters = filter.getParameters();
                if (parameters) {
                    result[name] = parameters;
                }
            }
        }, this);

        return result;
    },

    /**
     * Apply filter parameters stored in state
     *
     * @param state
     * @private
     * @return {*}
     */
    _applyState: function(state) {
        _.each(this.filters, function(filter, name) {
            if (name in state) {
                var filterState = state[name];
                if (!_.isObject(filterState)) {
                    filterState = {
                        '[value]': filterState
                    }
                }
                this.enableFilter(filter.setParameters(filterState));
            } else if ('__' + name in state) {
                this.enableFilter(filter.reset());
            } else {
                this.disableFilter(filter.reset());
            }
        }, this);

        return this;
    }
});
