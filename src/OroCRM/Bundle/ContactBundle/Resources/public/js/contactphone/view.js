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
            '<% _.each(contactphones, function(p, i) { %>' + 
                '<option <% if (p.get("primary")) { %> selected="selected" <% } %> value=<%= p.get("id") %>><%= p.get("phone") %></option>' +
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
            
            this.displaySelect2(options.isRelatedContact);
            this.phonesList.on('select2-init', _.bind(function() {
                this.displaySelect2(options.isRelatedContact);
            }, this));

            this.phonesList.closest('.controls').append(this.phonePlain);
            this.phonesList.on('change', _.bind(function(e) {
                if ($(e.target.selectedOptions).val() == "") {
                    this.showPlain(false);
                } else {
                    this.phonePlain.hide();
                }
            }, this));

            this.listenTo(this.collection, 'reset', this.render);

            this.render(!options.isRelatedContact);
            this.phonesList.trigger('change');
            this.phonePlain.css('margin-top', '0px');
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
                this.collection.setContactId(contactId);
                this.collection.fetch();
            }
        },

        /**
         * Render list and or input field
         */
        render: function(hide) {
            if (this.collection.models.length > 0) {
                this.showPhoneOptions();
            } else {                
                this.showPlain(hide);
            }
        },
        
        /**
         * Show plain phone input field
         */        
        showPlain: function(hide) {
            if (hide) {
                this.phonesList.hide();
                this.displaySelect2(false);
                $('#uniform-' + this.phonesList[0].id).hide();
                this.phonePlain.css('margin-top', '0px');
            }
                this.phonePlain.css('margin-top', '12px');
                this.phonePlain.show();
        },

        /**
         * Show phone seleciton dropdown
         */
        showPhoneOptions: function() {
                this.phonesList.show();
                this.displaySelect2(true);
                $('#uniform-' + this.phonesList[0].id).show();
                this.phonesList.find('option[value!=""]').remove();
                this.phonesList.append(this.phonesListTemplate({contactphones: this.collection.models}));
                this.phonesList.trigger('change');
                this.phonePlain.hide();
                this.phonePlain.val('');
                this.phonePlain.css('margin-top', '12px');
        }
    });
});
