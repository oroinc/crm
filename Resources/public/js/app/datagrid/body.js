var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};

/**
 * Datagrid body widget
 *
 * @class   OroApp.Datagrid.Body
 * @extends Backgrid.Body
 */
OroApp.Datagrid.Body = Backgrid.Body.extend({
    /** @property {Function} */
    rowClickAction: undefined,

    /** @property {String} */
    rowClickActionClass: 'row-click-action',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        options = options || {};

        if (options.rowClickAction) {
            this.rowClickAction = options.rowClickAction;
        }

        Backgrid.Body.prototype.initialize.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    render: function() {
        Backgrid.Body.prototype.render.apply(this, arguments);
        this.delegateRowClickEvents();
        if (this.rowClickAction) {
            this.$('tr').addClass(this.rowClickActionClass);
        }
        return this;
    },

    /**
     * Delegates row click events
     *
     * @protected
     */
    delegateRowClickEvents: function() {
        var self = this;
        _.each(this.rows, function(row) {
            row.delegateEvents({
                'click': function(e) {
                    var rowElement = row.$el.get(0);
                    var targetElement = e.target;
                    var targetParentElement = $(e.target).parent().get(0);
                    self.trigger('rowClicked', row, e);
                    if (rowElement == targetElement || rowElement == targetParentElement) {
                        self.trigger('rowClicked', row, e);
                        self.runRowClickAction(row);
                    }
                }
            });
        }, this);
    },

    /**
     * Run row click action if it exists
     *
     * @protected
     */
    runRowClickAction: function(row) {
        if (this.rowClickAction) {
            var action = new this.rowClickAction({
                model: row.model
            });
            action.run();
        }
    }
});
