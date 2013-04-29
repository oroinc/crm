var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Cell = OroApp.Datagrid.Cell || {};

/**
 * Datetime column cell. Added missing behavior.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   OroApp.Datagrid.Cell.MomentCell
 * @extends Backgrid.Extension.MomentCell
 */
OroApp.Datagrid.Cell.MomentCell = Backgrid.Extension.MomentCell.extend({

    /** @property {Backgrid.CellFormatter} */
    formatter: OroApp.Datagrid.Cell.Formatter.MomentFormatter,

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
