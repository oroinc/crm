OroApp.DatagridPagination = OroApp.View.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    className: 'pagination pagination-centered',

    /** @property */
    windowSize: 10,

    /** @property */
    template: _.template(
        '<label class="dib">Page:</label>' +
        '<ul class="icons-holder">' +
            '<% _.each(handles, function (handle) { %>' +
                '<li <% if (handle.className) { %>class="<%= handle.className %>"<% } %>>' +
                    '<a href="#" <% if (handle.title) {%> title="<%= handle.title %>"<% } %>>' +
                        '<% if (handle.wrapClass) {%>' +
                            '<i <% if (handle.wrapClass) { %>class="<%= handle.wrapClass %>"<% } %>>' +
                                '<%= handle.label %>' +
                            '</i>' +
                        '<% } else { %>' +
                            '<%= handle.label %>' +
                        '<% } %>' +
                    '</a>' +
                '</li>' +
            '<% }); %>' +
        '</ul>' +
        '<label class="dib">of <%= pageSize %> | <%= totalRecords %> records</label>'
    ),

    /** @property */
    events: {
        "click a": "changePage"
    },

    /** @property */
    fastForwardHandleConfig: {
        prev: {
            label: 'Prev',
            wrapClass: 'icon-chevron-left hide-text'
        },
        next: {
            label: 'Next',
            wrapClass: 'icon-chevron-right hide-text'
        }
    },

    /** @property */
    initOptionRequires: ['collection'],

    /**
     * Initializer.
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Object} options.fastForwardHandleConfig
     * @param {Integer} options.windowSize
     */
    initialize: function (options) {
        this.collection = options.collection;
        this.listenTo(this.collection, "add", this.render);
        this.listenTo(this.collection, "remove", this.render);
        this.listenTo(this.collection, "reset", this.render);
        OroApp.View.prototype.initialize.call(this, options);
    },

    /**
     * jQuery event handler for the page handlers. Goes to the right page upon clicking.
     *
     * @param {Event} e
     */
    changePage: function (e) {
        e.preventDefault();

        var label = $(e.target).text();
        var ffConfig = this.fastForwardHandleConfig;

        var collection = this.collection;

        if (ffConfig) {
            var prevLabel = _.has(ffConfig.prev, 'label') ? ffConfig.prev.label : undefined;
            var nextLabel = _.has(ffConfig.next, 'label') ? ffConfig.next.label : undefined;
            switch (label) {
                case prevLabel:
                    if (collection.hasPrevious()) collection.getPreviousPage();
                    return;
                case nextLabel:
                    if (collection.hasNext()) collection.getNextPage();
                    return;
            }
        }

        var state = collection.state;
        var pageIndex = $(e.target).text() * 1 - state.firstPage;
        collection.getPage(state.firstPage === 0 ? pageIndex : pageIndex + 1);
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
        var state = collection.state;

        // convert all indices to 0-based here
        var lastPage = state.lastPage ? state.lastPage : state.firstPage;
        lastPage = state.firstPage === 0 ? lastPage : lastPage - 1;
        var currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
        var windowStart = Math.floor(currentPage / this.windowSize) * this.windowSize;
        var windowEnd = windowStart + this.windowSize;
        windowEnd = windowEnd <= lastPage ? windowEnd : lastPage + 1;

        if (collection.mode !== "infinite") {
            for (var i = windowStart; i < windowEnd; i++) {
                handles.push({
                    label: i + 1,
                    title: "No. " + (i + 1),
                    className: currentPage === i ? "active" : undefined
                });
            }
        }

        var ffConfig = this.fastForwardHandleConfig;

        if (ffConfig.prev) {
            handles.unshift({
                label: _.has(ffConfig.prev, 'label') ? ffConfig.prev.label : undefined,
                wrapClass: _.has(ffConfig.prev, 'wrapClass') ? ffConfig.prev.wrapClass : undefined,
                className: collection.hasPrevious() ? undefined : "disabled"
            });
        }

        if (ffConfig.next) {
            handles.push({
                label: _.has(ffConfig.next, 'label') ? ffConfig.next.label : undefined,
                wrapClass: _.has(ffConfig.next, 'wrapClass') ? ffConfig.next.wrapClass : undefined,
                className: collection.hasNext() ? void 0 : "disabled"
            });
        }

        return handles;
    },
    render: function() {
        this.$el.empty();

        this.$el.append($(this.template({
            handles: this.makeHandles(),
            pageSize: this.collection.state.pageSize,
            totalRecords: this.collection.state.totalRecords
        })));

        return this;
    }
});
