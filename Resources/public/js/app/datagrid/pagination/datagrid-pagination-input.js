OroApp.DatagridPaginationInput = OroApp.DatagridPagination.extend({
    /** @property */
    template: _.template(
        '<label class="dib">Page:</label>' +
        '<ul class="icons-holder">' +
            '<% _.each(handles, function (handle) { %>' +
                '<li <% if (handle.className) { %>class="<%= handle.className %>"<% } %>>' +
                    '<% if (handle.type == "input") { %>' +
                        '<input type="text" value="<%= collectionState.firstPage == 0 ? collectionState.currentPage + 1 : collectionState.currentPage  %>" />' +
                    '<% } else { %>' +
                        '<a href="#" <% if (handle.title) {%> title="<%= handle.title %>"<% } %>>' +
                            '<% if (handle.wrapClass) {%>' +
                                '<i <% if (handle.wrapClass) { %>class="<%= handle.wrapClass %>"<% } %>>' +
                                    '<%= handle.label %>' +
                                '</i>' +
                            '<% } else { %>' +
                                '<%= handle.label %>' +
                            '<% } %>' +
                        '</a>' +
                    '<% } %>' +
                '</li>' +
            '<% }); %>' +
        '</ul>' +
        '<label class="dib">of <%= collectionState.totalPages %> | <%= collectionState.totalRecords %> records</label>'
    ),

    /** @property */
    events: {
        "click a": "changePage",
        "change input": "changePageByInput",
        "keypress input": "validatePageInputKey"
    },

    windowSize: 0,

    /**
     * @inheritDoc
     */
    initialize: function (options) {
        OroApp.DatagridPagination.prototype.initialize.call(this, options);
    },

    /**
     * Apply change of pagination page input
     *
     * @param {Event} e
     */
    changePageByInput: function(e) {
        e.preventDefault();

        var pageIndex = parseInt($(e.target).val());
        var collection = this.collection;
        var state = collection.state;

        if (_.isNaN(pageIndex)) {
            $(e.target).val(state.currentPage);
            return;
        }

        pageIndex = state.firstPage == 0 ? pageIndex - 1  : pageIndex;
        if (pageIndex < state.firstPage) {
            pageIndex = state.firstPage;
            $(e.target).val(state.firstPage == 0 ? state.firstPage + 1 : state.firstPage);
        } else if (state.lastPage <= pageIndex) {
            pageIndex = state.lastPage;
            $(e.target).val(state.firstPage == 0 ? state.lastPage + 1 : state.lastPage);
        }

        if (state.currentPage !== pageIndex) {
            collection.getPage(pageIndex);
        }
    },

    /**
     * Validate key pressed on page input
     *
     * @param {Event} e
     */
    validatePageInputKey: function(e) {
        var keyCode = e.keyCode || e.which;
        var keyChar = String.fromCharCode(keyCode);
        if (keyCode !== 13 && !/[0-9]/.test(keyChar)) {
            e.returnValue = false;
            e.preventDefault();
        }
    },

    /**
     * Internal method to create a list of page handle objects for the template
     * to render them.
     *
     * @return Array.<Object> an array of page handle objects hashes
     */
    makeHandles: function () {
        var handles = [];
        var collection = this.collection;
        var ffConfig = this.fastForwardHandleConfig;

        handles.push({
            type: 'input'
        });

        return OroApp.DatagridPagination.prototype.makeHandles.call(this, handles);
    }
});
