define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const types = ['recency', 'frequency', 'monetary'];
    let rows = 0;
    const rowTemplate = _.template(
        '<tr>' +
            '<td class="rfm-cell-index"></td>' +
            '<td class="rfm-cell-recency"></td>' +
            '<td class="rfm-cell-frequency"></td>' +
            '<td class="rfm-cell-monetary"></td>' +
            '<td class="action-cell">' +
                '<a href="#" class="action-delete" title="<%- _.__("oro.analytics.delete_row") %>">' +
                    '<i class="fa-close hide-text"></i>' +
                '</a>' +
            '</td>' +
        '</tr>'
    );

    return function(options) {
        const $el = options._sourceElement;
        const $enableEl = $el.find('#' + options.rfm_enable_id);
        const $table = $el.find('.grid tbody');
        const rfmElements = {};

        for (let i = 0; i < types.length; i++) {
            const type = types[i];
            const typeEl = $el.find('.rfm-' + type);

            rfmElements[type] = {
                el: typeEl,
                template: typeEl.data('prototype'),
                isIncreasing: typeEl.data('increasing')
            };
        }

        const getIndexInput = function($row) {
            return $row.find('[name$="[category_index]"]');
        };

        const getInputBy = function($cell, isIncreasing) {
            if (isIncreasing) {
                return $cell.find('[name$="[max_value]"]');
            } else {
                return $cell.find('[name$="[min_value]"]');
            }
        };

        const getRfmCell = function($row, type) {
            return $row.find('.rfm-cell-' + type);
        };

        const getInvisibleInput = function($row, type) {
            return getInputBy(getRfmCell($row, type), !rfmElements[type].isIncreasing);
        };

        const getVisibleInput = function($row, type) {
            return getInputBy(getRfmCell($row, type), rfmElements[type].isIncreasing);
        };

        const recalculateIdx = function() {
            const rows = $table.find('tr');
            const rowsNum = rows.length;
            for (let i = 0; i < rowsNum; i++) {
                const $row = $(rows[i]);
                const idx = i + 1;
                let postfix = '';
                getIndexInput($row).val(idx);

                if (i === 0) {
                    postfix = '<br><small>' + __('oro.analytics.best') + '</small>';
                }
                if (i === rowsNum - 1) {
                    postfix = '<br><small>' + __('oro.analytics.worst') + '</small>';
                }
                $row.find('.rfm-cell-index').html(idx + postfix);
            }
        };

        const setupChangeVal = function($row, type) {
            getVisibleInput($row, type).keyup(function() {
                const nextRow = $row.next();
                const nextInput = getInvisibleInput(nextRow, type);
                const val = $(this).val();

                nextInput.val(val);

                getRfmCell(nextRow, type).find('strong').html(val ? val : __('oro.analytics.na'));
            });
        };

        const createSettingsRow = function(recency, frequency, monetary, append) {
            const $row = $(rowTemplate());
            $(recency).appendTo($row.find('.rfm-cell-recency'));
            $(frequency).appendTo($row.find('.rfm-cell-frequency'));
            $(monetary).appendTo($row.find('.rfm-cell-monetary'));

            if (append) {
                $row.appendTo($table);
            }

            return $row;
        };

        const getPreparedTemplate = function(type) {
            return rfmElements[type].template.replace(/__name__/g, rows);
        };

        const addRow = function() {
            const $newRow = createSettingsRow(
                getPreparedTemplate('recency'),
                getPreparedTemplate('frequency'),
                getPreparedTemplate('monetary')
            );

            const lastRow = $table.find('tr').last();
            if (lastRow.length) {
                $newRow.insertBefore($(lastRow));
            } else {
                $newRow.appendTo($table);
            }

            rows++;

            return $newRow;
        };

        const decorateFirstRow = function(row) {
            const $row = $(row);
            $row.find('.action-delete').hide();

            for (let i = 0; i < types.length; i++) {
                const type = types[i];
                const $input = getVisibleInput($row, type);

                $input.prop('type', 'text');
                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('oro.analytics.less') + ' </span>').insertBefore($input);
                } else {
                    $('<span>' + __('oro.analytics.more') + ' </span>').insertBefore($input);
                }

                setupChangeVal($row, type);
            }

            rows++;
        };

        const decorateRow = function(row) {
            const $row = $(row);

            for (let i = 0; i < types.length; i++) {
                const type = types[i];
                const $input = getVisibleInput($row, type);

                $input.prop('type', 'text');
                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('oro.analytics.from') + '</span> <strong></strong>' +
                    '<br><span>' + __('oro.analytics.to') + ' </span>').insertBefore($input);
                } else {
                    $('<span>' + __('oro.analytics.from') + ' </span>').insertBefore($input);
                    $('<br><span> ' + __('oro.analytics.to') + '</span> <strong></strong>').insertAfter($input);
                }

                setupChangeVal($row, type);
            }

            rows++;
        };

        const decorateLastRow = function(row) {
            const $row = $(row);
            $row.find('.action-delete').hide();

            for (let i = 0; i < types.length; i++) {
                const type = types[i];
                const $input = getVisibleInput($row, type);

                if (rfmElements[type].isIncreasing) {
                    $('<span>' + __('oro.analytics.more') + '</span> <strong></strong>').insertBefore($input);
                } else {
                    $('<span>' + __('oro.analytics.less') + '</span> <strong></strong>').insertBefore($input);
                }
            }

            rows++;
        };

        const refresh = function() {
            $el.find('input').trigger('keyup');
            recalculateIdx();
        };

        const adoptExistingRecords = function() {
            const existingRRows = rfmElements.recency.el.find('.rfm-settings-row');
            const existingFRows = rfmElements.frequency.el.find('.rfm-settings-row');
            const existingMRows = rfmElements.monetary.el.find('.rfm-settings-row');
            const totalRows = existingRRows.length;
            const lastRowIdx = totalRows - 1;

            if (totalRows < 2) {
                $table.empty();
                decorateLastRow(addRow());
                decorateFirstRow(addRow());
            } else {
                decorateFirstRow(createSettingsRow(existingRRows[0], existingFRows[0], existingMRows[0], true));
                if (totalRows > 2) {
                    for (let i = 1; i < totalRows - 1; i++) {
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

        const enableHandler = function() {
            if ($enableEl.is(':checked')) {
                $el.addClass('rfm-enabled');
            } else {
                $el.removeClass('rfm-enabled');
            }
        };

        $enableEl.on('click', enableHandler);

        adoptExistingRecords();
        enableHandler();

        const removeValidateInfo = function() {
            $el.find('.alert-error').hide();
            $el.find('.rfm-settings-data').find('.validation-error').removeClass('validation-error');
        };

        $el.closest('form').on('submit', removeValidateInfo);
    };
});
