$(function() {
    var collection = new OroApp.PageableCollection([], OroApp.scope.options.collection);

    // Initialize a new Grid instance
    var grid = new OroApp.Datagrid(_.extend({
        collection: collection
        //el: $('#backgrid')
    }, OroApp.scope.options.grid));

    // Render the grid and attach the root to your HTML document
    $("#backgrid").append(grid.render().$el);
    //userGrid.show();
    collection.fetch();
});
