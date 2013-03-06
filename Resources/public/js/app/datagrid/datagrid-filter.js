/**
 * View that represents all grid filters
 *
 * @class   OroApp.DatagridFilterList
 * @extends Backbone.View
 */
OroApp.DatagridFilterList = Backbone.View.extend({
    /** @property */
    filters: {},

    /** @property */
    addButtonTemplate: _.template(
        '<a href="#" class="btn btn-link btn-group"><%= addButtonHint %></a>' +
        '<select id="add-filter-select" multiple>' +
            '<% _.each(filters, function (filter, name) { %>' +
                '<option value="<%= name %>" <% if (filter.enabled) { %>selected<% } %>>' +
                    '<%= filter.hint %>' +
                '</option>' +
            '<% }); %>' +
        '</select>'
    ),

    /** @property */
    filterSelector: '#add-filter-select',

    /** @property */
    addButtonHint: 'Add filter',

    /** @property */
    events: {
        'change #add-filter-select': '_processFilterStatus'
    },

    /**
     * Initialize filter list options
     *
     * @param {Object} options
     */
    initialize: function(options)
    {
        this.collection = options.collection;

        if (options.filters) {
            this.filters = options.filters;
        }

        for (var name in this.filters) {
            this.filters[name] = new (this.filters[name])();
            this.listenTo(this.filters[name], "changedData", this._reloadCollection);
            this.listenTo(this.filters[name], "disabled", this.disableFilter);
        }

        if (options.addButtonHint) {
            this.addButtonHint = options.addButtonHint;
        }

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Activate/deactivate all filter depends on its status
     *
     * @private
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
    },

    /**
     * Disable filter
     *
     * @param {OroApp.DatagridFilter} filter
     */
    disableFilter : function(filter) {
        filter.disable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).removeAttr('selected');
    },

    /**
     * Render filter list
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        for (var name in this.filters) {
            this.filters[name].render();
            if (!this.filters[name].enabled) {
                this.filters[name].hide();
            }
            this.$el.append(this.filters[name].$el);
        }

        this.$el.append(this.addButtonTemplate({
            filters: this.filters,
            addButtonHint: this.addButtonHint
        }));

        this.trigger("rendered");

        return this;
    },

    /**
     * Reload collection data with current filters
     *
     * @private
     */
    _reloadCollection: function() {
        var filterParams = {};
        for (var name in this.filters) {
            var filter = this.filters[name];
            if (filter.enabled) {
                var parameters = filter.getParameters();
                if (parameters) {
                    filterParams[name] = parameters;
                }
            }
        }
        this.collection.state.filters = filterParams;
        this.collection.state.currentPage = 1;
        this.collection.fetch();
    }
});

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
    className: 'btn-group',

    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <input type="text" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
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
        'click a.disable-filter': 'disable'
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
     * Enable filter
     */
    enable: function() {
        if (!this.enabled) {
            this.enabled = true;
            this.show();
            if (this.hasValue()) {
                this.trigger('changedData');
            }
        }
    },

    /**
     * Disable filter
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
    },

    /**
     * Reset filter form elements
     */
    reset: function() {
        this.$(this.parameterSelectors.value).val('');
    },

    /**
     * Show filter
     */
    show: function() {
        this.$el.css('display', 'inline-block');
    },

    /**
     * Hide filter
     */
    hide: function() {
        this.$el.css('display', 'none');
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
     * Get list of filter parameters
     *
     * @return {Object}
     */
    getParameters: function() {
        return {
            '[value]': this.$(this.parameterSelectors.value).val()
        };
    }
});

/**
 * Choice filter: filter type as option + filter value as string
 *
 * @class   OroApp.DatagridChoiceFilter
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridChoiceFilter = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>:' +
            '<% _.each(choices, function (hint, value) { %>' +
                '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
            '<% }); %>' +
            '<input type="text" name="value" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
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
        'click a.disable-filter': 'disable'
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
     */
    reset: function() {
        this.$(this.parameterSelectors.type).val('');
        this.$(this.parameterSelectors.value).val('');
    },

    /**
     * Get list of filter parameters
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

/**
 * Date filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridDateFilter
 * @extends OroApp.DatagridChoiceFilter
 */
OroApp.DatagridDateFilter = OroApp.DatagridChoiceFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>:' +
            '<% _.each(choices, function (hint, value) { %>' +
                '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
            '<% }); %>' +
            'date from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        type:  'input[name="type"]:checked',
        value_start: 'input[name="start"]',
        value_end: 'input[name="end"]'
    },

    /** @property */
    events: {
        'change input[name="type"]': '_updateOnType',
        'change input[name="start"]': '_update',
        'change input[name="end"]': '_update',
        'click a.disable-filter': 'disable'
    },

    /**
     * Check if filter contain value
     *
     * @return {Boolean}
     */
    hasValue: function() {
        return this.$(this.parameterSelectors.value_start).val() != ''
            || this.$(this.parameterSelectors.value_end).val() != '';
    },

    /**
     * Reset filter form elements
     */
    reset: function() {
        this.$(this.parameterSelectors.type).val('');
        this.$(this.parameterSelectors.value_start).val('');
        this.$(this.parameterSelectors.value_end).val('');
    },

    /**
     * Get list of filter parameters
     *
     * @return {Object}
     */
    getParameters: function() {
        return {
            '[type]':  this.$(this.parameterSelectors.type).val(),
            '[value][start]': this.$(this.parameterSelectors.value_start).val(),
            '[value][end]': this.$(this.parameterSelectors.value_end).val()
        };
    }
});

/**
 * Datetime filter: filter type as option + interval begin and end dates
 *
 * @class   OroApp.DatagridDateTimeFilter
 * @extends OroApp.DatagridDateFilter
 */
OroApp.DatagridDateTimeFilter = OroApp.DatagridDateFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>:' +
            '<% _.each(choices, function (hint, value) { %>' +
                '<input type="radio" name="type" value="<%= value %>" /><%= hint %>' +
            '<% }); %>' +
            'datetime from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

/**
 * Select filter: filter value as select option
 *
 * @class   OroApp.DatagridSelectFilter
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridSelectFilter = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        value: 'select'
    },

    /** @property */
    events: {
        'change select': '_update',
        'click a.disable-filter': 'disable'
    },

    /** @property */
    options: {},

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
                options: this.options
            })
        );
        return this;
    }
});

/**
 * Multiple select filter: filter values as multiple select options
 *
 * @class   OroApp.DatagridMultiSelectFilter
 * @extends OroApp.DatagridSelectFilter
 */
OroApp.DatagridMultiSelectFilter = OroApp.DatagridSelectFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;" multiple>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    )
});