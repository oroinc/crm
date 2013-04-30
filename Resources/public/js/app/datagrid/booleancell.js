var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Column = OroApp.Datagrid.Column || {};

/**
 * Boolean column cell
 *
 * @class   OroApp.Datagrid.BooleanCell
 * @extends Backgrid.BooleanCell
 */
OroApp.Datagrid.BooleanCell = Backgrid.BooleanCell.extend({
    /** @property {Boolean} */
    editable: false,

    /** @property {Object} */
    editor: _.template("<input type='checkbox' <%= checked ? checked='checked' : '' %> <%= editable ? '' : 'disabled' %> />'"),

    /**
     * Initialize editable flag
     */
    initialize: function() {
        Backgrid.BooleanCell.prototype.initialize.apply(this, arguments);
        this.editable = this.column.get("editable");
    },

    /**
     * Renders a checkbox and check it if the model value of this column is true, uncheck otherwise.
     */
    render: function () {
        this.$el.empty();
        this.currentEditor = $(this.editor({
            checked:  this.formatter.fromRaw(this.model.get(this.column.get("name"))),
            editable: this.editable
        }));
        this.$el.append(this.currentEditor);
        return this;
    },

    /**
     * Simple focuses the checkbox and add an `editor` CSS class to the cell.
     */
    enterEditMode: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.enterEditMode.apply(this, arguments);
        }
    },

    /**
     * Removed the `editor` CSS class from the cell.
     */
    exitEditMode: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.exitEditMode.apply(this, arguments);
        }
    },

    /**
     * Set true to the model attribute if the checkbox is checked, false otherwise.
     */
    save: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.save.apply(this, arguments);
        }
    }
});
