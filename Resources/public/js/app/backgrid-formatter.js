// Replace standard formatter method of Backgrid.MomentFormatter
var originalFromRawFunction = Backgrid.Extension.MomentFormatter.prototype.fromRaw;
Backgrid.Extension.MomentFormatter.prototype.fromRaw = function (rawData) {
    if (rawData == null) {
        return '';
    }

    return originalFromRawFunction.apply(this, arguments);
}


// Replace standard formatter method of Backgrid.CellFormatter
var originalFromRawFunction = Backgrid.CellFormatter.prototype.fromRaw;
Backgrid.CellFormatter.prototype.fromRaw = function(rawData) {
    if (rawData == null) {
        return '';
    }

    return originalFromRawFunction.apply(this, arguments);
}
