var OroAddress = Backbone.Model.extend({
    defaults: {
        'label': '',
        'street': '',
        'street2': '',
        'city': '',
        'country': '',
        'postalCode': '',
        'state': '',
        'isPrimary': false,
        'types': []
    }
});
