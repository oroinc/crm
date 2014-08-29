/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var _ = require('underscore'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        var removeEvents = _.map(options, function (gridName) {
            return 'datagrid:removeRow:' + gridName;
        });

        _.each(removeEvents, function (event) {
            mediator.on(removeEvents.join(' '), function () {
                var refreshEvents = _.map(_.without(options, _.last(event.split(':'))), function (gridName) {
                    return 'datagrid:doRefresh:' + gridName;
                });

                _.each(refreshEvents, function (event) {
                    mediator.trigger(event);
                });
            });
        });
    };
});
