var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.StateCollection = Backbone.Collection.extend({
    url: '/app_dev.php/api/rest/latest/windows',
    model: Oro.widget.StateModel
});

Oro.widget.States = new Oro.widget.StateCollection();