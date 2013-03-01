OroApp.Datagrid = Backgrid.Grid.extend({
    initialize: function(options) {
        delete(options.className);
        Backgrid.Grid.prototype.initialize.apply(this, arguments);
    }
});
