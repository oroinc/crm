// list of filters
OroApp.FilterList = Backbone.View.extend({
    /** @property */
    filters: {},

    /** @property */
    addButtonTemplate: _.template(
        '<span id="add-filter-panel">' +
            '<a href="#" class="btn btn-link btn-group"><%= addButtonHint %></a>' +
            '<select id="add-filter-select" multiple>' +
                '<% _.each(filters, function (filter, name) { %>' +
                    '<option value="<%= name %>" <% if (filter.enabled) { %>selected<% } %>>' +
                        '<%= filter.hint %>' +
                    '</option>' +
                '<% }); %>' +
            '</select>' +
        '</span>'
    ),

    /** @property */
    filterSelector: '#add-filter-select',

    /** @property */
    addButtonHint: 'Add filter',

    /** @property */
    events: {
        'change select': 'processFilterStatus'
    },

    initialize: function(options)
    {
        this.collection = options.collection;

        if (options.filters) {
            this.filters = options.filters;
        }

        for (var name in this.filters) {
            this.filters[name] = new (this.filters[name])();
            this.listenTo(this.filters[name], "changedData", this.reloadCollection);
            this.listenTo(this.filters[name], "disabled", this.disableFilter);
        }

        if (options.addButtonHint) {
            this.addButtonHint = options.addButtonHint;
        }

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    processFilterStatus: function(e) {
        e.preventDefault();
        var activeFilters = this.$(this.filterSelector).val();

        _.each(this.filters, function(filter, name) {
            if (!filter.enabled && _.indexOf(activeFilters, name) != -1) {
                this.enableFilter(filter);
            } else if (filter.enabled && _.indexOf(activeFilters, name) == -1) {
                this.disableFilter(filter);
            }
        }, this);
    },

    enableFilter: function(filter) {
        filter.enable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).attr('selected', 'selected');
    },

    disableFilter : function(filter) {
        filter.disable();
        var optionSelector = this.filterSelector + ' option[value="' + filter.name + '"]';
        this.$(optionSelector).removeAttr('selected');
    },

    render: function () {
        this.$el.empty();

        for (var name in this.filters) {
            var filter = this.filters[name];
            if (filter.enabled) {
                this.$el.append(filter.render().$el);
            }
        }

        this.$el.append(this.addButtonTemplate({
            filters: this.filters,
            addButtonHint: this.addButtonHint
        }));

        return this;
    },

    reloadCollection: function() {
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

// basic filter
OroApp.Filter = Backbone.View.extend({
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
    enabled: true,

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
        'change input': 'update',
        'click a.disable-filter': 'disable'
    },

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint
            })
        );
        return this;
    },

    update: function(e) {
        e.preventDefault();
        this.trigger('changedData');
    },

    enable: function() {
        if (!this.enabled) {
            this.enabled = true;
            this.show();
            if (this.hasValue()) {
                this.trigger('changedData');
            }
        }
    },

    disable: function() {
        if (this.enabled) {
            this.enabled = false;
            this.hide();
            this.trigger('disabled', this);
            if (this.hasValue()) {
                this.trigger('changedData');
            }
        }
    },

    show: function() {
        this.$el.show();
    },

    hide: function() {
        this.$el.hide();
    },

    hasValue: function() {
        return this.$(this.parameterSelectors.value).val() != '';
    },

    getParameters: function() {
        return {
            '[value]': this.$(this.parameterSelectors.value).val()
        };
    }
});

// choice filter: filter type as option + filter value as string
OroApp.ChoiceFilter = OroApp.Filter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %>' +
                    '<option value="<%= value %>"><%= hint %></option>' +
                '<% }); %>' +
            '</select>' +
            '<input type="text" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        type:  'select',
        value: 'input'
    },

    /** @property */
    events: {
        'change select': 'updateOnSelect',
        'change input': 'update',
        'click a.disable-filter': 'disable'
    },

    /** @property */
    choices: {},

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint,
                choices:   this.choices
            })
        );
        return this;
    },

    updateOnSelect: function(e) {
        e.preventDefault();
        if (this.hasValue()) {
            this.trigger('changedData');
        }
    },

    getParameters: function() {
        return {
            '[type]':  this.$(this.parameterSelectors.type).val(),
            '[value]': this.$(this.parameterSelectors.value).val()
        };
    }
});

// date filter: filter type as option + date value as string
OroApp.DateFilter = OroApp.ChoiceFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'date is <input type="text" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// datetime filter: filter type as option + datetime value as string
OroApp.DateTimeFilter = OroApp.DateFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'datetime is <input type="text" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// date range filter: filter type as option + interval begin and end dates
OroApp.DateRangeFilter = OroApp.ChoiceFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'date from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        type: 'select',
        value_start: 'input[name="start"]',
        value_end: 'input[name="end"]'
    },

    /** @property */
    events: {
        'change select': 'updateOnSelect',
        'change input[name="start"]': 'update',
        'change input[name="end"]': 'update',
        'click a.disable-filter': 'disable'
    },

    hasValue: function() {
        return this.$(this.parameterSelectors.value_start).val() != ''
            || this.$(this.parameterSelectors.value_end).val() != '';
    },

    getParameters: function() {
        return {
            '[type]':  this.$(this.parameterSelectors.type).val(),
            '[value][start]': this.$(this.parameterSelectors.value_start).val(),
            '[value][end]': this.$(this.parameterSelectors.value_end).val()
        };
    }
});

// datetime range filter: filter type as option + interval begin and end datetimes
OroApp.DateTimeRangeFilter = OroApp.DateRangeFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= hint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'datetime from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<a href="#" class="disable-filter">X</a>' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// select filter: filter value as select option
OroApp.SelectFilter = OroApp.Filter.extend({
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
        'change select': 'update',
        'click a.disable-filter': 'disable'
    },

    /** @property */
    options: {},

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint,
                options:   this.options
            })
        );
        return this;
    }
});
