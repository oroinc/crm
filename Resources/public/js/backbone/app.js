/**
 * Main Oro Application backbone.js namespace
 */
window.OroApp = {

    /**
     * Pack object to string
     *
     * Object {foo: 'x', 'bar': 'y'} will be converted to string "foo=x&bar=y".
     *
     * @param {Object} object
     * @return {String}
     */
    packToQueryString: function(object) {
        return $.param(object);
    },

    /**
     * Unpack string to object. Reverse from packToQueryString.
     *
     * @param {String} string
     * @return {Object}
     */
    unpackFromQueryString: function(string) {
        var result = {};
        var vars = string.split("&");
        for (var i=0; i<vars.length; i++) {
            var pair = vars[i].split("=");
            pair[0] = decodeURIComponent(pair[0]);
            pair[1] = decodeURIComponent(pair[1]);
            // If first entry with this name
            if (typeof result[pair[0]] === "undefined") {
                result[pair[0]] = pair[1];
                // If second entry with this name
            } else if (typeof result[pair[0]] === "string") {
                result[pair[0]] = [ result[pair[0]], pair[1] ];
                // If third or later entry with this name
            } else {
                result[pair[0]].push(pair[1]);
            }
        }
        return result;
    },

    /**
     * Invert object keys.
     *
     * Example of usage:
     *
     * OroApp.mirrorKeys({foo: 'x', bar: 'y'}, {foo: 'f', bar: 'b'})
     * will return {f: 'x', b: 'y'}
     *
     * @param {Object} object
     * @param {Object} keys
     * @return {Object}
     */
    invertKeys: function(object, keys) {
        var result = _.extend({}, object);
        for (key in keys) {
            var mirrorKey, baseKey;
            baseKey = key;
            mirrorKey = keys[key];

            if (baseKey in result) {
                result[mirrorKey] = result[baseKey]
                delete result[baseKey];
            }
        }
        return result;
    }
};
