// Oro Application grid prototype
OroApp.Datagrid = Backgrid.Grid.extend({
    footer: OroApp.Paginator,
    initialize: function(options, foo) {
        Backgrid.Grid.prototype.initialize.apply(this, arguments);
    }
});
