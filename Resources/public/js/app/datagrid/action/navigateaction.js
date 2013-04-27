var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Action = OroApp.Datagrid.Action || {};

/**
 * Navigate action. Changes window location to url, from getLink method
 *
 * @class   OroApp.Datagrid.Action.NavigateAction
 * @extends OroApp.Datagrid.Action.ModelAction
 */
OroApp.Datagrid.Action.NavigateAction = OroApp.Datagrid.Action.ModelAction.extend({

    /**
     * If `true` then created launcher will be complete clickable link,
     * If `false` redirection will be delegated to execute method.
     *
     * @property {Boolean}
     */
    useDirectLauncherLink: true,

    /**
     * Initialize launcher options with url
     *
     * @param {Object} options
     * @param {Boolean} options.useDirectLauncherLink
     */
    initialize: function(options) {
        OroApp.Datagrid.Action.ModelAction.prototype.initialize.apply(this, arguments);

        if (options.useDirectLauncherLink) {
            this.useDirectLauncherLink = options.useDirectLauncherLink;
        }

        if (this.useDirectLauncherLink) {
            this.launcherOptions = _.extend({
                link: this.getLink(),
                runAction: false
            }, this.launcherOptions);
        }
    },

    /**
     * Execute redirect
     */
    execute: function() {
        if (OroApp.hashNavigation) {
            OroApp.hashNavigation.prototype.setLocation(this.getLink());
        } else {
            window.location.href = this.getLink();
        }
    }
});
