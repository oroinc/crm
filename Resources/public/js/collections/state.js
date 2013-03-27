var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.StateCollection = Backbone.Collection.extend({
    model: Oro.widget.StateModel
});

Oro.widget.States = new Oro.widget.StateCollection();