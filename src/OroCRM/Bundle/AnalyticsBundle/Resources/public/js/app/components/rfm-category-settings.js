/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery'),
        __ = require('orotranslation/js/translator');

    return function (options) {
        var $el = options._sourceElement.find('#' + options.containerId),
            isIncreasing = options.isIncreasing,
            rowTemplate = $el.data('prototype'),
            rows = 0;

        var getInputBy = function($row, isIncreasing) {
            if (isIncreasing) {
                return $row.find('[name$="[max_value]"]');
            } else {
                return $row.find('[name$="[min_value]"]');
            }
        };

        var getIndexInput = function($row) {
            return $row.find('[name$="[index]"]');
        };

        var getInvisibleInput = function($row) {
            return getInputBy($row, !isIncreasing);
        };

        var getVisibleInput = function($row) {
            return getInputBy($row, isIncreasing);
        };

        var decorateFirstRow = function($row) {
            rows++;

            $row.find('.action-delete').hide();

            var input = getVisibleInput($row);
            input.prop('type', 'text');

            var pre = '<b></b> &dash; ';
            if (isIncreasing) {
                $(pre + '<span>' + __('orocrm.analytics.less') + ' </span>').insertBefore(input);
            } else {
                $(pre + '<span>' + __('orocrm.analytics.more') + ' </span>').insertBefore(input);
            }

            setupChangeVal($row);
        };

        var setupChangeVal = function($row) {
            if (!$row.data('initialized')) {
                getVisibleInput($row).change(function () {
                    var nextRow = $row.next(),
                        nextInput = getInvisibleInput(nextRow),
                        val = $(this).val();

                    nextInput.val(val);
                    nextRow.find('strong').html(val ? val : __('orocrm.analytics.na'));
                });

                $row.data('initialized', true);
            }
        };

        var decorateRow = function($row) {
            rows++;

            var input = getVisibleInput($row);

            input.prop('type', 'text');
            var pre = '<b></b> &dash; ';
            if (isIncreasing) {
                $(pre + '<span>' + __('orocrm.analytics.from')
                    + ' <strong></strong> ' + __('orocrm.analytics.to') + ' </span>').insertBefore(input);
            } else {
                $(pre + '<span>' + __('orocrm.analytics.from') + ' </span>').insertBefore(input);
                $('<span> ' + __('orocrm.analytics.to') + ' <strong></strong></span>').insertAfter(input);
            }

            setupChangeVal($row);
        };

        var decorateLastRow = function($row) {
            rows++;

            $row.find('.actions').hide();

            var input = getVisibleInput($row);
            var pre = '<b></b> &dash; ';
            if (isIncreasing) {
                $(pre + '<span>' + __('orocrm.analytics.more') + ' <strong></strong></span>').insertBefore(input);
            } else {
                $(pre + '<span>' + __('orocrm.analytics.less') + ' <strong></strong></span>').insertBefore(input);
            }
        };

        var recalculateIdx = function() {
            var rows = $el.find('.rfm_settings_row');
            for (var i = 0; i < rows.length; i++) {
                var $row = $(rows[i]);
                var idx = i+1;
                getIndexInput($row).val(idx);
                $row.find('b').html(idx);
            }
        };

        var addRow = function($row) {
            var $newRow = $(rowTemplate.replace(/__name__/g, rows));
            if ($row !== undefined) {
                $newRow.insertAfter($row);
            } else {
                $newRow.appendTo($el);
            }
            rows++;

            return $newRow;
        };

        var render = function() {
            var existingRows = $el.find('.rfm_settings_row');
            if (existingRows.length < 2) {
                $el.empty();
                decorateFirstRow(addRow());
                decorateLastRow(addRow());
            } else {
                decorateFirstRow($(existingRows[0]));
                if (existingRows.length > 2) {
                    for (var i = 1; i < existingRows.length - 1; i++) {
                        decorateRow($(existingRows[i]));
                    }
                }
                decorateLastRow($(existingRows[existingRows.length - 1]));
            }

            refresh();
        };

        var refresh = function() {
            $el.find('input').trigger('change');
            recalculateIdx();
        };

        $el.on('click', '.action-add', function() {
            decorateRow(addRow($(this).closest('.rfm_settings_row')));
            refresh();
        });

        $el.on('click', '.action-delete', function() {
            $(this).closest('.rfm_settings_row').remove();
            refresh();
        });

        render();
    };
});
