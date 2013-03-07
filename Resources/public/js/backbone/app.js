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
     * @param {String} query
     * @return {Object}
     */
    unpackFromQueryString: function(query) {
        var setValue = function (root, path, value) {
            if (path.length > 1) {
                var dir = path.shift();
                if (typeof root[dir] == 'undefined') {
                    root[dir] = path[0] == '' ? [] : {};
                }
                arguments.callee(root[dir], path, value);
            } else {
                if (root instanceof Array) {
                    root.push(value);
                } else {
                    root[path] = value;
                }
            }
        };
        var nvp = query.split('&');
        var data = {};
        for (var i = 0 ; i < nvp.length ; i++) {
            var pair = nvp[i].split('=');
            var name = decodeURIComponent(pair[0]);
            var value = decodeURIComponent(pair[1]);

            var path = name.match(/(^[^\[]+)(\[.*\]$)?/);
            var first = path[1];
            if (path[2]) {
                //case of 'array[level1]' || 'array[level1][level2]'
                path = path[2].match(/(?=\[(.*)\]$)/)[1].split('][')
            } else {
                //case of 'name'
                path = [];
            }
            path.unshift(first);

            setValue(data, path, value);
        }
        return data;
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
        for (var key in keys) {
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
