var Oro = Oro || {};

Oro.pagestate = Oro.pagestate || {};

Oro.pagestate = Backbone.Model.extend({
    defaults: {
        formData: {},
        pageId: ''
    },

    collect: function () {
        this.set(formData, $('form[data-collect=true] :input').serializeArray());
    }
});