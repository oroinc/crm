/**
 * Datetime formatter which supports null values
 *
 * @class OroApp.DatagridDateTimeFormatter
 * @extends Backgrid.Extension.MomentFormatter
 * @constructor
 */
OroApp.DatagridDateTimeFormatter = function (options) {
    _.extend(this, this.defaults, options);
};

OroApp.DatagridDateTimeFormatter.prototype = new Backgrid.Extension.MomentFormatter;
_.extend(OroApp.DatagridDateTimeFormatter.prototype, {
    /**
     * Converts datetime values from the model for display.
     *
     * @param {*} rawData
     * @return {string}
     */
    fromRaw: function (rawData) {
        if (rawData == null) {
            return '';
        }

        return Backgrid.Extension.MomentFormatter.prototype.fromRaw.apply(this, arguments);
    }
});

// replace standard formatter
Backgrid.Extension.MomentCell.prototype.formatter = OroApp.DatagridDateTimeFormatter;
