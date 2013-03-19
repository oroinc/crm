/**
 * Loading mask widget
 *
 * @class   OroApp.LoadingMask
 * @extends OroApp.View
 */
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
     *
     * @return {this}
     */
    show: function() {
        this.$el.show();
        return this;
    },

    /**
     * Hide loading mask
     *
     * @return {this}
     */
    hide: function() {
        this.$el.hide();
        return this;
    },

    /**
     * Render loading mask
     *
     * @return {this}
     */
    render: function() {
        this.$el.empty();
        this.$el.append(this.template());
        this.hide();
        return this;
    }
});
