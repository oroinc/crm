Oro = Oro || {};
Oro.RegionUpdater = Oro.RegionUpdater || {};

Oro.RegionUpdater.View = Backbone.View.extend({
    events: {
        'change': 'selectionChanged'
    },

    /**
     * Constructor
     *
     * @param options {Object}
     */
    initialize: function (options) {
        this.target = $(options.target);
        this.template = $('#region-chooser-template').html();
    },

    /**
     * Trigger change event
     */
    sync: function () {
        var el = $(this.el);

        if (el.val() == '') {
            el.trigger('change');
        }
    },

    /**
     * onChange event listener
     *
     * @param e {Object}
     */
    selectionChanged: function (e) {
        var self = this;

        var countryId = $(e.currentTarget).val();

        this.collection.setCountryId(countryId);
        this.collection.fetch({
            success: function () {
                var regionSelect = $(self.target);

                regionSelect.val('').trigger('change');
                regionSelect.find('option[value!=""]').remove();
                regionSelect.append(_.template(self.template, {regions: self.collection.models}));
            }
        });

    }
});
