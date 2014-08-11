define(['underscore'], function (_) {

    /**
     * @class orocrmchannel.utils.channelFormUtils
     */
    return {
        /**
         * Prepares select2 data from HTMLSelectElement's options array
         *
         * @param {array} items
         *
         * @returns Object
         */
        prepareSelect2Data: function (items) {
            var data = {
                more: false,
                results: []
            };

            _.each(items, function (key, value) {
                data.results.push(value);
            });

            return data;
        }
    }
});
