/* global define */
define(['jquery', 'underscore', 'backbone', 'orocrm/contactphone/collection', 'jquery.select2'],
function($, _, Backbone, ContactPhoneCollection) {
    'use strict';

    /**
     * @export  orocrm/contactphone/view
     * @class   orocrm.contactphone.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /**
         * @property ContactPhoneCollection
         */
        collection: null,

        /**
         * Contact element
         *
         * @property
         */
        contact: null,

        /**
         * Select element of contact's phones numbers.
         *
         * @property
         */
        phoneSelector: null,

        /**
         * Input field for phone number
         *
         * @property
         */
        phonePlain: null,

        /**
         * Phone list template
         *
         * @property
         */
        phonesSelectorTemplate: _.template(
            '<% _.each(contactPhones, function(phone) { %>' +
                '<option <% if (phone.get("primary")) { %> selected="selected" <% } %> value=<%= phone.get("id") %>><%= _.escape(phone.get("phone")) %></option>' +
            '<% }); %>'
        ),

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function(options) {
            if (!options.contact) {
                throw new Error('Contact must be specified');
            }
            this.contact = options.contact;

            if (!options.phoneSelector) {
                throw new Error('Phone selector must be specified');
            }
            this.phoneSelector = options.phoneSelector;

            if (!options.phonePlain) {
                throw new Error('Phone text field must be specified');
            }
            this.phonePlain = options.phonePlain;

            this.initializeCollection();

            // put plain phone field in the correct place
            this.phoneSelector.closest('.controls').append(this.phonePlain);
            this.phonePlain.css('margin-top', '12px');

            // listen to change of selectors
            this.contact.on('change', _.bind(this.contactChanged, this));
            this.phoneSelector.on('change', _.bind(this.redrawPhonePlain, this));

            // redraw element on collection update
            this.listenTo(this.collection, 'reset', this.render);

            this.render();
        },

        /**
         * Initialize and fill collection with default models
         */
        initializeCollection: function() {
            var models = [];
            _.each(this.phoneSelector.find('option'), function(option) {
                if (option.value) {
                    models.push({
                        'id':      option.value,
                        'phone':   option.label,
                        'primary': option.selected
                    });
                }
            }, this);
            this.collection = new ContactPhoneCollection(models);
        },

        /**
         * @returns {boolean}
         */
        isShowPhonePlain: function() {
            return this.collection.models.length == 0 || this.phoneSelector.val() == "";
        },

        /**
         * Redraw
         */
        redrawPhonePlain: function() {
            if (this.isShowPhonePlain()) {
                this.phonePlain.show();
            } else {
                this.phonePlain.hide();
                this.phonePlain.val('');
            }
        },

        /**
         * onChange event listener
         */
        contactChanged: function() {
            var contactId = this.contact.val();
            if (contactId) {
                this.collection.setContactId(contactId);
                this.collection.fetch();
            } else {
                this.collection.reset();
            }
        },

        syncPhoneSelector: function() {
            this.phoneSelector.find('option[value!=""]').remove();
            this.phoneSelector.append(this.phonesSelectorTemplate({contactPhones: this.collection.models}));
            this.phoneSelector.trigger('change');
        },

        /**
         * Render list and or input field
         */
        render: function() {
            this.syncPhoneSelector();
            this.redrawPhonePlain();
        }
    });
});
