var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Datagrid header cell
 *
 * @class   Oro.Datagrid.HeaderCell
 * @extends Backgrid.HeaderCell
 */
Oro.Datagrid.HeaderCell = Backgrid.HeaderCell.extend({

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

    /** @property {Boolean} */
    allowNoSorting: false,

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
    },

    /**
     * Click on column name to perform sorting
     *
     * @param {Event} e
     */
    onClick: function (e) {
        e.preventDefault();

        var columnName = this.column.get("name");

        if (this.column.get("sortable")) {
            if (this.direction() === "ascending") {
                this.sort(columnName, "descending", function (left, right) {
                    var leftVal = left.get(columnName);
                    var rightVal = right.get(columnName);
                    if (leftVal === rightVal) {
                        return 0;
                    }
                    else if (leftVal > rightVal) { return -1; }
                    return 1;
                });
            }
            else if (this.allowNoSorting && this.direction() === "descending") {
                this.sort(columnName, null);
            }
            else {
                this.sort(columnName, "ascending", function (left, right) {
                    var leftVal = left.get(columnName);
                    var rightVal = right.get(columnName);
                    if (leftVal === rightVal) {
                        return 0;
                    }
                    else if (leftVal < rightVal) { return -1; }
                    return 1;
                });
            }
        }
    }
});
