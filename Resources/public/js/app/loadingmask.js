OroApp.LoadingMask = OroApp.View.extend({

    /** @property */
    template:_.template(
        '<div id="loading-wrapper" class="loading-wrapper" style="display: block;"></div>' +
        '<div id="loading-frame" class="loading-frame" style="display: block;">' +
            '<div class="box well">' +
                '<img src="/bundles/oroui/img/loadinfo.net.gif" alt="">' +
                '<img src="/bundles/oroui/img/loader.gif" alt="">' +
                'Loading . . .' +
            '</div>' +
        '</div>'
    ),

    /**
     * Show loading mask
     */
    show: function() {
        this.$el.show();
        return this;
    },

    /**
     * Hide loading mask
     */
    hide: function() {
        this.$el.hide();
        return this;
    },

    /**
     * Render loading mask
     */
    render: function() {
        this.$el.empty();
        this.$el.append(this.template());
        this.hide();
        return this;
    }
});
