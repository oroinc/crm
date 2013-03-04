OroApp.Datagrid = Backgrid.Grid.extend({
    /** @property */
    className: 'grid table-hover table table-bordered table-condensed',

    /** @property */
    header: OroApp.DatagridHeader,

    /**
     * Initialize datagrid
     *
     * @param {Object} options
     */
    initialize: function(options) {
        Backgrid.Grid.prototype.initialize.apply(this, arguments);
    }
});
