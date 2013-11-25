/* global define */
define(['underscore', 'backbone', 'jquery.select2'],
function(_, Backbone) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  orocrm/contactphone/view
     * @class   orocrm.contactphone.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({

        /**
         * List of events
         *
         * @property
         */        
        events: {
            'change': 'selectionChanged'
        },

        /**
         * Select element of contact's phones numbers.
         *
         * @property
         */
        phonesList: null,

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
        phonesListTemplate: _.template(
            '<% _.each(contactphones, function(phone) { %>' + 
                '<option <% if (phone.get("primary")) { %> selected="selected" <% } %> value=<%= phone.get("id") %>><%= phone.get("phone") %></option>' +
            '<% }); %>'
        ),         
        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function(options) {
            
            this.phonesList = $(options.target);
            this.phonePlain = $(options.simpleEl);
            this.isRelatedContact = options.isRelatedContact;
            
            this.displaySelect2(this.isRelatedContact);
            this.phonesList.on('select2-init', _.bind(function() {
                this.displaySelect2(this.isRelatedContact);
            }, this));

            this.phonesList.on('change', _.bind(function(e) {
                if (this.phonesList.val() == "") {
                    this.showPlain();
                } else {
                    this.hidePlain();
                }
            }, this));

            this.listenTo(this.collection, 'reset', this.render);

            this.render();
        },

        /**
         * Show/hide select 2 element
         *
         * @param {Boolean} display
         */
        displaySelect2: function(display) {
            if (display) {
                this.phonesList.select2('container').show();
            } else {
                this.phonesList.select2('container').hide();
            }
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function(e) {
            var contactId = $(e.currentTarget).val();
            if (contactId) {
                this.phonesList.find('option[value!=""]').remove();
                this.collection.setContactId(contactId);
                this.collection.fetch();
            }
        },

        /**
         * Render list and or input field
         */
        render: function() {

            if (this.collection.models.length > 0) {
                this.showPhonesList();
            } else {      
                this.showPlain(); 
            }
            this.phonesList.trigger('change');

            if (!this.isRelatedContact && this.collection.models.length == 0) {
                this.hidePhonesList();
            }
        },
        
        /**
         * Show plain phone input field
         */        
        showPlain: function() {
            this.phonePlain.css('margin-top', '12px');            
            this.phonesList.closest('.controls').append(this.phonePlain);
            this.phonePlain.show();
        },
        
        hidePlain: function() {
            this.phonePlain.css('margin-top', '0px');
            this.phonePlain.hide();
            this.phonePlain.val('');
        },  

        /**
         * Show phone seleciton dropdown
         */
        showPhonesList: function() {
            this.phonePlain.css('margin-top', '12px');
            this.phonesList.closest('.controls').append(this.phonePlain);
            this.phonesList.show();
            this.displaySelect2(true);
            $('#uniform-' + this.phonesList[0].id).show();
            this.phonesList.find('option[value!=""]').remove();
            this.phonesList.append(this.phonesListTemplate({contactphones: this.collection.models}));
        },

        hidePhonesList: function() {
            this.phonePlain.css('margin-top', '0px');
            this.phonesList.closest('.controls').prepend(this.phonePlain);
            this.phonesList.hide();
            this.displaySelect2(false);
            $('#uniform-' + this.phonesList[0].id).hide();
        },
    });
});
