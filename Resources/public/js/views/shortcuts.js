var navigation = navigation || {};
navigation.shortcut = navigation.shortcut || {};

navigation.shortcut.MainView = Backbone.View.extend({
    options: {
        el: '.shortcuts .input-large',
        source: null
    },

    events: {
        'change': 'onChange'
    },

    data: {},

    initialize: function() {
        this.$el.val('');
        this.$el.typeahead({
            source: this.source.bind(this)
        });
        this.$form = this.$el.closest('form');
    },

    source: function(query, process) {
        if (_.isArray(this.options.source)) {
            process(this.options.source);
        } else {
            var url = this.options.source + (this.options.source.charAt(this.options.source.length - 1) === '/' ? '' : '/')
            $.get(url + encodeURIComponent(query), function(data) {
                this.data = data;
                var result = [];
                _.each(data, function(item, key) {
                    result.push(key);
                });
                process(result);
            }.bind(this));
        }
    },

    onChange: function() {
        var key = this.$el.val();
        if (!_.isUndefined(this.data[key])) {
            var dataItem = this.data[key];
            this.$form.attr("action", dataItem.url).submit();
        }
    }
});
