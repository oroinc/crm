OroApp.DatagridHeaderCell = Backgrid.HeaderCell.extend({

    /** @property */
    template:_.template(
        '<% if (sortable) { %>' +
            '<a href="#">' +
                '<%= label %> ' +
                '<span class="caret"></b>' +
            '</a>' +
        '<% } else { %>' +
            '<span><%= label %></span>' + // wrap label into span otherwise underscore will not render it
        '<% } %>'
    ),

    /**
     * Renders a header cell with a sorter and a label.
     */
    render: function () {
        this.$el.empty();

        this.$el.append($(this.template({
            label: this.column.get("label"),
            sortable: this.column.get("sortable")
        })));

        return this;
    }
});
