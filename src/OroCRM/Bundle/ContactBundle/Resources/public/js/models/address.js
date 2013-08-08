var OroAddress = Backbone.Model.extend({
    defaults: {
        'label': '',
        'firstName': '',
        'lastName': '',
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
