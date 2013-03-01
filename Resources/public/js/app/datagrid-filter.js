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
            var parameterValue = filter.getParameterValue();
            if (parameterValue) {
                filterParams[filter.inputName] = parameterValue;
            }
        }
        this.collection.state.filters = filterParams;
        this.collection.fetch();
    }
});

// basic filter
OroApp.Filter = Backbone.View.extend({
    /** @property */
    tagName: "div",

    /** @property */
    className: "btn-group",

    /** @property */
    template: _.template(
        '<div class="btn">' +
            '<%= inputHint %>: <input type="text" name="<%= inputName %>" value="" />' +
            '<span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    inputName: 'input_name',

    /** @property */
    inputHint: 'Input Hint',

    events: {
        "change input": "update"
    },

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                inputName: this.inputName,
                inputHint: this.inputHint
            })
        );
        return this;
    },

    update: function(e) {
        e.preventDefault();
        this.trigger("changedData");
    },

    getParameterValue: function() {
        return this.$('input').val();
    }
});
