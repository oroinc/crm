var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Cell = OroApp.Datagrid.Cell || {};
OroApp.Datagrid.Cell.Formatter = OroApp.Datagrid.Cell.Formatter || {};

/**
 * Cell formatter with fixed fromRaw method
 *
 * @class   OroApp.Datagrid.Cell.Formatter.CellFormatter
 * @extends Backgrid.CellFormatter
 */
OroApp.Datagrid.Cell.Formatter.CellFormatter = function () {};

OroApp.Datagrid.Cell.Formatter.CellFormatter.prototype = new Backgrid.CellFormatter;
_.extend(OroApp.Datagrid.Cell.Formatter.CellFormatter.prototype, {
    /**
     * @inheritDoc
     */
    fromRaw: function (rawData) {
        if (rawData == null) {
            return '';
        }
        return Backgrid.CellFormatter.prototype.fromRaw.apply(this, arguments);
    }
});
