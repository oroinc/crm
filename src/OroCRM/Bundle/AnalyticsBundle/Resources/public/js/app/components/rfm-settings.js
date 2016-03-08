define(function(require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var types = ['recency', 'frequency', 'monetary'];
    var rows = 0;
    var rowTemplate = _.template(
            '<tr>' +
                '<td class="rfm-cell-index"></td>' +
                '<td class="rfm-cell-recency"></td>' +
                '<td class="rfm-cell-frequency"></td>' +
                '<td class="rfm-cell-monetary"></td>' +
                '<td class="action-cell">' +
                    '<a href="#" class="action-delete" title="<%= _.__("orocrm.analytics.delete_row") %>">' +
                        '<i class="icon-remove hide-text"></i>' +
                    '</a>' +
                '</td>' +
            '</tr>'
        );

    return function(options) {
        var $el = options._sourceElement;
        var $enableEl = $el.find('#' + options.rfm_enable_id);
        var $table = $el.find('.grid tbody');
        var rfmElements = {};

        for (var i = 0; i < types.length; i++) {
            var type = types[i];
            var typeEl = $el.find('.rfm-' + type);

            rfmElements[type] = {
                'el': typeEl,
                'template': typeEl.data('prototype'),
                'isIncreasing': typeEl.data('increasing')
            };
        }

        var getIndexInput = function($row) {
            return $row.find('[name$="[category_index]"]');
        };

        var getInputBy = function($cell, isIncreasing) {
            if (isIncreasing) {
                return $cell.find('[name$="[max_value]"]');
            } else {
                return $cell.find('[name$="[min_value]"]');
            }
        };

        var getRfmCell = function($row, type) {
            return $row.find('.rfm-cell-' + type);
        };

        var getInvisibleInput = function($row, type) {
            return getInputBy(getRfmCell($row, type), !rfmElements[type].isIncreasing);
        };

        var getVisibleInput = function($row, type) {
            return getInputBy(getRfmCell($row, type), rfmElements[type].isIncreasing);
        };

        var recalculateIdx = function() {
            var rows = $table.find('tr');
            var rowsNum = rows.length;
            for (var i = 0; i < rowsNum; i++) {
                var $row = $(rows[i]);
                var idx = i + 1;
                var postfix = '';
                getIndexInput($row).val(idx);

                if (i === 0) {
                    postfix = '<br><small>' + __('orocrm.analytics.best') + '</small>';
                }
                if (i === rowsNum - 1) {
                    postfix = '<br><small>' + __('orocrm.analytics.worst') + '</small>';
                }
                $row.find('.rfm-cell-index').html(idx + postfix);
            }
        };

        var setupChangeVal = function($row, type) {
            getVisibleInput($row, type).keyup(function() {
                var nextRow = $row.next();
                var nextInput = getInvisibleInput(nextRow, type);
                var val = $(this).val();

                nextInput.val(val);

                getRfmCell(nextRow, type).find('strong').html(val ? val : __('orocrm.analytics.na'));
            });
        };

        var createSettingsRow = function(recency, frequency, monetary, append) {
            var $row = $(rowTemplate());
            $(recency).appendTo($row.find('.rfm-cell-recency'));
            $(frequency).appendTo($row.find('.rfm-cell-frequency'));
            $(monetary).appendTo($row.find('.rfm-cell-monetary'));

            if (append) {
                $row.appendTo($table);
            }

            return $row;
        };

        var getPreparedTemplate = function(type) {
            return rfmElements[type].template.replace(/__name__/g, rows);
        };

        var addRow = function() {
            var $newRow = createSettingsRow(
                getPreparedTemplate('recency'),
                getPreparedTemplate('frequency'),
                getPreparedTemplate('monetary')
            );

            var lastRow = $table.find('tr').last();
            if (lastRow.length) {
                $newRow.insertBefore($(lastRow));
            } else {
                $newRow.appendTo($table);
            }

            rows++;

            return $newRow;
        };

        var decorateFirstRow = function(row) {
            var $row = $(row);
            $row.find('.action-delete').hide();

            for (var i = 0; i < types.length; i++) {
                var type = types[i];
                var $input = getVisibleInput($row, type);

                $input.prop('type', 'text');
                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('orocrm.analytics.less') + ' </span>').insertBefore($input);
                } else {
                    $('<span>' + __('orocrm.analytics.more') + ' </span>').insertBefore($input);
                }

                setupChangeVal($row, type);
            }

            rows++;
        };

        var decorateRow = function(row) {
            var $row = $(row);

            for (var i = 0; i < types.length; i++) {
                var type = types[i];
                var $input = getVisibleInput($row, type);

                $input.prop('type', 'text');
                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('orocrm.analytics.from') + '</span> <strong></strong>' +
                    '<br><span>' + __('orocrm.analytics.to') + ' </span>').insertBefore($input);
                } else {
                    $('<span>' + __('orocrm.analytics.from') + ' </span>').insertBefore($input);
                    $('<br><span> ' + __('orocrm.analytics.to') + '</span> <strong></strong>').insertAfter($input);
                }

                setupChangeVal($row, type);
            }

            rows++;
        };

        var decorateLastRow = function(row) {
            var $row = $(row);
            $row.find('.action-delete').hide();

            for (var i = 0; i < types.length; i++) {
                var type = types[i];
                var $input = getVisibleInput($row, type);

                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('orocrm.analytics.more') + '</span> <strong></strong>').insertBefore($input);
                } else {
                    $('<span>' + __('orocrm.analytics.less') + '</span> <strong></strong>').insertBefore($input);
                }
            }

            rows++;
        };

        var refresh = function() {
            $el.find('input').trigger('keyup');
            recalculateIdx();
        };

        var adoptExistingRecords = function() {
            var existingRRows = rfmElements.recency.el.find('.rfm-settings-row');
            var existingFRows = rfmElements.frequency.el.find('.rfm-settings-row');
            var existingMRows = rfmElements.monetary.el.find('.rfm-settings-row');
            var totalRows = existingRRows.length;
            var lastRowIdx = totalRows - 1;

            if (totalRows < 2) {
                $table.empty();
                decorateLastRow(addRow());
                decorateFirstRow(addRow());
            } else {
                decorateFirstRow(createSettingsRow(existingRRows[0], existingFRows[0], existingMRows[0], true));
                if (totalRows > 2) {
                    for (var i = 1; i < totalRows - 1; i++) {
                        decorateRow(createSettingsRow(existingRRows[i], existingFRows[i], existingMRows[i], true));
                    }
                }

                decorateLastRow(
                    createSettingsRow(
                        existingRRows[lastRowIdx],
                        existingFRows[lastRowIdx],
                        existingMRows[lastRowIdx],
                        true
                    )
                );
            }

            refresh();
        };

        $el.on('click', '.action-add', function() {
            if ($enableEl.is(':checked')) {
                decorateRow(addRow());
                refresh();
            }
        });

        $el.on('click', '.action-delete', function() {
            if ($enableEl.is(':checked')) {
                $(this).closest('tr').remove();
                refresh();
            }
        });

        var enableHandler = function() {
            if ($enableEl.is(':checked')) {
                $el.addClass('rfm-enabled');
            } else {
                $el.removeClass('rfm-enabled');
            }
        };

        $enableEl.on('click', enableHandler);

        adoptExistingRecords();
        enableHandler();

        var removeValidateInfo = function() {
            $el.find('.alert-error').hide();
            $el.find('.rfm-settings-data').find('.validation-error').removeClass('validation-error');
        };

        $el.closest('form').on('submit', removeValidateInfo);
    };
});
