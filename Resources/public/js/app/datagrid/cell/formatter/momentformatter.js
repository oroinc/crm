var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Cell = OroApp.Datagrid.Cell || {};
OroApp.Datagrid.Cell.Formatter = OroApp.Datagrid.Cell.Formatter || {};

/**
 * Formatter for date and time. Fixed formatting method.
 *
 * @class   OroApp.Datagrid.Cell.Formatter.MomentFormatter
 * @extends Backgrid.Extension.MomentFormatter
 */
OroApp.Datagrid.Cell.Formatter.MomentFormatter = function (options) {
    _.extend(this, this.defaults, options);
}

OroApp.Datagrid.Cell.Formatter.MomentFormatter.prototype = new Backgrid.Extension.MomentFormatter;
_.extend(OroApp.Datagrid.Cell.Formatter.MomentFormatter.prototype, {
    /**
     * @inheritDoc
     */
    fromRaw: function (rawData) {
        if (!rawData) {
            return '';
        }
        return Backgrid.Extension.MomentFormatter.prototype.fromRaw.apply(this, arguments);
    }
});
