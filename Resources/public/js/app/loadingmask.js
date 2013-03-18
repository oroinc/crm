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
     * Is visible
     *
     * @return {Number}
     */
    isVisible: function() {
        return this.$el.filter(':visible').length;
    },

    /**
     * Show loading mask
     */
    show: function() {
        if (!this.isVisible()) {
            this.$el.show();
        }
        return this;
    },

    /**
     * Hide loading mask
     */
    hide: function() {
        if (this.isVisible()) {
            this.$el.hide();
        }
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
