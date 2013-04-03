// Replace standard formatter method of Backgrid.MomentFormatter
var originalMomentFormatterFromRawFunction = Backgrid.Extension.MomentFormatter.prototype.fromRaw;
Backgrid.Extension.MomentFormatter.prototype.fromRaw = function (rawData) {
    if (rawData == null) {
        return '';
    }

    return originalMomentFormatterFromRawFunction.apply(this, arguments);
}

// Replace standard formatter method of Backgrid.CellFormatter
var originalCellFormatterFromRawFunction = Backgrid.CellFormatter.prototype.fromRaw;
Backgrid.CellFormatter.prototype.fromRaw = function(rawData) {
    if (rawData == null) {
        return '';
    }

    return originalCellFormatterFromRawFunction.apply(this, arguments);
}
