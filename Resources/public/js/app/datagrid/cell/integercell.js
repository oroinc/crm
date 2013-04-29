var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Cell = OroApp.Datagrid.Cell || {};

/**
 * Integer column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   OroApp.Datagrid.Cell.IntegerCell
 * @extends Backgrid.IntegerCell
 */
OroApp.Datagrid.Cell.IntegerCell = Backgrid.IntegerCell.extend({
    /**
     * @inheritDoc
     */
    enterEditMode: function (e) {
        if (this.column.get("editable")) {
            e.stopPropagation();
        }
        return Backgrid.Extension.MomentCell.prototype.enterEditMode.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    exitEditMode: function (e) {
        if (this.editable) {
            this.trigger("edited", this);
        }
        return Backgrid.Extension.MomentCell.prototype.exitEditMode.apply(this, arguments);
    }
});
