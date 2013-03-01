// list of filters
OroApp.FilterList = Backbone.View.extend({
    /** @property */
    filters: [],

    /** @property */
    addButtonTemplate: _.template('<a href="#" class="btn btn-link btn-group"><%= addButtonHint %></a>'),

    /** @property */
    addButtonHint: 'Add filter',

    // set list of filters
    initialize: function(options)
    {
        if (options.filters) {
            this.filters = options.filters;
        }

        if (options.addButtonHint) {
            this.addButtonHint = options.addButtonHint;
        }

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    render: function () {
        this.$el.empty();

        for (var i = 0; i < this.filters.length; i++) {
            var filter = new (this.filters[i])();
            this.$el.append(filter.render().$el);
        }

        this.$el.append(this.addButtonTemplate({
            addButtonHint: this.addButtonHint
        }));

        return this;
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
            '<%= inputHint %>: <input type="text" name="<%= inputName %>"><span class="caret"></span>' +
        '</div>'
    ),

    /** @property */
    inputName: 'input_name',

    /** @property */
    inputHint: 'Input Hint',

    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                inputName: this.inputName,
                inputHint: this.inputHint
            })
        );
        return this;
    }
});
