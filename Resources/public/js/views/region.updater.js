Oro = Oro || {};
Oro.RegionUpdater = Oro.RegionUpdater || {};

Oro.RegionUpdater.View = Backbone.View.extend({
    events: {
        'change': 'selectionChanged'
    },

    initialize: function (options) {
        this.target = $(options.target);
        this.template = $('#region-chooser-template').html();
    },

    selectionChanged: function (e) {
        var self = this;

        var countryId = $(e.currentTarget).val();

        this.collection.setCountryId(countryId);
        this.collection.fetch({
            success: function () {
                var regionSelect = $(self.target);

                regionSelect.find('option[value!=""]').remove();
                regionSelect.append(_.template(self.template, {regions: self.collection.models}));
            }
        });

    }
});
