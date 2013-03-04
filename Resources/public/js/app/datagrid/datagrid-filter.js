// list of filters
OroApp.FilterList = Backbone.View.extend({
    /** @property */
    filters: [],

    /** @property */
    addButtonTemplate: _.template('<a href="#" class="btn btn-link btn-group"><%= addButtonHint %></a>'),

    /** @property */
    addButtonHint: 'Add filter',

    initialize: function(options)
    {
        this.collection = options.collection;

        if (options.filters) {
            this.filters = options.filters;
        }

        for (var i = 0; i < this.filters.length; i++) {
            this.filters[i] = new (this.filters[i])();
            this.listenTo(this.filters[i], "changedData", this.reloadCollection);
        }

        if (options.addButtonHint) {
            this.addButtonHint = options.addButtonHint;
        }

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    render: function () {
        this.$el.empty();

        for (var i = 0; i < this.filters.length; i++) {
            this.$el.append(this.filters[i].render().$el);
        }

        this.$el.append(this.addButtonTemplate({
            addButtonHint: this.addButtonHint
        }));

        return this;
    },

    reloadCollection: function() {
        var filterParams = {};
        for (var i = 0; i < this.filters.length; i++) {
            var filter = this.filters[i];
            var parameters = filter.getParameters();
            if (parameters) {
                filterParams[filter.inputName] = parameters;
            }
        }
        this.collection.state.filters = filterParams;
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
            '<%= inputHint %>: <input type="text" value="" style="width:80px;" />' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    inputName: 'input_name',

    /** @property */
    inputHint: 'Input Hint',

    /** @property */
    parameterSelectors: {
        value: 'input'
    },

    /** @property */
    events: {
        'change input': 'update'
    },

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                inputHint: this.inputHint
            })
        );
        return this;
    },

    update: function(e) {
        e.preventDefault();
        this.trigger('changedData');
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
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<input type="text" value="" style="width:80px;" />' +
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
        'change input': 'update'
    },

    /** @property */
    choices: {},

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                inputHint: this.inputHint,
                choices:   this.choices
            })
        );
        return this;
    },

    updateOnSelect: function(e) {
        e.preventDefault();
        if (this.$(this.parameterSelectors.value).val()) {
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
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'date is <input type="text" value="" style="width:80px;" />' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// datetime filter: filter type as option + datetime value as string
OroApp.DateTimeFilter = OroApp.DateFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'datetime is <input type="text" value="" style="width:80px;" />' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// date range filter: filter type as option + interval begin and end dates
OroApp.DateRangeFilter = OroApp.ChoiceFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'date from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
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
        'change input[name="end"]': 'update'
    },

    updateOnSelect: function(e) {
        e.preventDefault();
        if (this.$(this.parameterSelectors.value_start).val()
            || this.$(this.parameterSelectors.value_end).val()
        ) {
            this.trigger('changedData');
        }
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
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(choices, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            'datetime from <input type="text" name="start" value="" style="width:80px;" />' +
            'to <input type="text" name="end" value="" style="width:80px;" />' +
            '<span class="caret"></span>' +
        '</div>'
    )
});

// select filter: filter value as select option
OroApp.SelectFilter = OroApp.Filter.extend({
    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= inputHint %>: <select style="width:150px;">' +
                '<option value=""></option>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    parameterSelectors: {
        value: 'select'
    },

    /** @property */
    events: {
        'change select': 'update'
    },

    /** @property */
    options: {},

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                inputHint: this.inputHint,
                options:   this.options
            })
        );
        return this;
    }
});
