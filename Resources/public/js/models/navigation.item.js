var navigation = navigation || {};

navigation.Item = Backbone.Model.extend({
    defaults: {
        title: '',
        url: null,
        position: null,
        type: null
    },

    url: function(a) {
        var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
        base +=  (base.charAt(base.length - 1) === '/' ? '' : '/') + this.get('type');
        if (this.isNew()) {
            return base;
        }
        return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + 'ids/' + encodeURIComponent(this.id);
    },

    updateTitle: function(title, defaultTitle) {
        this.attributes.title = _.isObject(title) ? JSON.stringify(title) : '{"template": "' + defaultTitle + '"}';
    },

    clone: function() {
        var clone = _.clone(this);
        clone.attributes = _.clone(this.attributes);

        return clone;
    },

    updateTitleIfExists: function()
    {
        if (!_.isUndefined(this.attributes.title_rendered)) {
            // to avoid changing title passed by reference
            var data = this.clone();
            data.attributes.title = data.attributes.title_rendered;
        }
        else {
            var data = this;
        }

        return data;
    }
});
