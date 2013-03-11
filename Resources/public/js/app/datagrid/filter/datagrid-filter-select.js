/**
 * Select filter: filter value as select option
 *
 * @class   OroApp.DatagridFilterSelect
 * @extends OroApp.DatagridFilter
 */
OroApp.DatagridFilterSelect = OroApp.DatagridFilter.extend({
    /** @property */
    template: _.template(
        '<div class="btn filter-select">' +
            '<%= hint %>: <select style="width:150px;">' +
            '<option value=""><%= placeholder %></option>' +
            '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
            '<a href="#" class="disable-filter" />' +
            '</div>'
    ),

    /** @property */
    parameterSelectors: {
        value: 'select'
    },

    /** @property */
    events: {
        'change select': '_update',
        'click a.disable-filter': 'onClickDisable'
    },

    /** @property */
    options: {},

    /** @property */
    placeholder: 'All',

    /** @property */
    select2Element: 'select',

    /** @property */
    select2Config: {
        width: 'off',
        dropdownCssClass: 'select-filter-dropdown'
    },

    /**
     * Render filter template
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();
        this.$el.append(
            this.template({
                hint: this.hint,
                options: this.options,
                placeholder: this.placeholder
            })
        );

        this.initSelect2();

        return this;
    },

    /**
     * Create Select2 instance
     */
    initSelect2: function() {
        // create select2 instance
        var select2Object = this.$el.find(this.select2Element).select2(this.select2Config);

        var data = {
            filterElement: this.$el,
            select2Config: this.select2Config
        };
        select2Object.on('open', data, this.recalculateDropdownPosition);
    },

    /**
     * Recalculate position of the select filter drop down relative to filter container
     *
     * @param event
     */
    recalculateDropdownPosition: function(event) {
        var filterElement = event.data.filterElement,
            dropdown = $('.' + event.data.select2Config.dropdownCssClass),
            body = filterElement.closest('body'),
            offset = filterElement.offset(),
            dropLeft = offset.left,
            dropWidth = dropdown.outerWidth(false),
            viewPortRight = $(window).scrollLeft() + $(window).width(),
            enoughRoomOnRight = dropLeft + dropWidth <= viewPortRight;


        if (body.css('position') !== 'static') {
            var bodyOffset = body.offset();
            dropLeft -= bodyOffset.left;
        }

        if (!enoughRoomOnRight) {
            dropLeft = offset.left + width - dropWidth;
        }

        dropdown.css('left', dropLeft);
    }
});
