/**
 * Datagrid header cell
 *
 * @class   OroApp.Datagrid.HeaderCell
 * @extends Backgrid.HeaderCell
 */
OroApp.Datagrid.HeaderCell = Backgrid.HeaderCell.extend({

    /** @property */
    template:_.template(
        '<% if (sortable) { %>' +
            '<a href="#">' +
                '<%= label %> ' +
                '<span class="caret"></span>' +
            '</a>' +
        '<% } else { %>' +
            '<span><%= label %></span>' + // wrap label into span otherwise underscore will not render it
        '<% } %>'
    ),

    /**
     * Initialize.
     *
     * Add listening "reset" event of collection to able catch situation when header cell should update it's sort state.
     */
    initialize: function() {
        Backgrid.HeaderCell.prototype.initialize.apply(this, arguments);
        this.collection.on('reset', this._initCellDirection, this);
    },

    /**
     * Inits cell direction when collections loads first time.
     *
     * @param collection
     * @private
     */
    _initCellDirection: function(collection) {
        if (collection == this.collection) {
            var state = collection.state;
            var direction = null;
            if (this.column.get('sortable') && state.sortKey == this.column.get('name')) {
                if (1 == state.order) {
                    direction = 'descending';
                } else if (-1 == state.order) {
                    direction = 'ascending';
                }
            }
            if (direction != this.direction()) {
                this.direction(direction);
            }
        }
    },

    /**
     * Renders a header cell with a sorter and a label.
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el.append($(this.template({
            label: this.column.get("label"),
            sortable: this.column.get("sortable")
        })));

        return this;
    }
});
