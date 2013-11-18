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
        events: {
            'change': 'selectionChanged'
        },

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function(options) {
            
            this.target = $(options.target);
            this.$simpleEl = $(options.simpleEl);

            this.target.closest('.controls').append(this.$simpleEl);

            this.target.on('change', _.bind(function(e) {
                if ($(e.target.selectedOptions).val() == -1) {
                    this.showPlain();    
                }
            }, this));
            
            this.showSelect = options.showSelect;
            this.template = $('#contactphone-chooser-template').html();
            this.$simpleEl.attr('type', 'text');

            if (!this.showSelect) {
                this.$simpleEl.show();
            } else {
                this.$simpleEl.hide();
            }

            this.displaySelect2(this.showSelect);
            this.target.on('select2-init', _.bind(function() {
                this.displaySelect2(this.showSelect);
            }, this));

            this.listenTo(this.collection, 'reset', this.render);
        },

        /**
         * Show/hide select 2 element
         *
         * @param {Boolean} display
         */
        displaySelect2: function(display) {
            if (display) {
                this.target.select2('container').show();
            } else {
                this.target.select2('container').hide();
            }
        },

        getInputLabel: function(el) {
            return el.parent().parent().find('label');
        },

        /**
         * Trigger change event
         */
        sync: function() {
            if (this.target.val() == '' && this.$el.val() != '') {
                this.$el.trigger('change');
            }
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function(e) {
            var contactId = $(e.currentTarget).val();
            console.log(contactId);
            if (contactId) {
                this.collection.setContactId(contactId);
                this.collection.fetch();
            } else {
                this.showPlain();
            }
        },

        render: function() {

            if (this.collection.models.length > 0) {
                this.showOptions();
            } else {                
                this.showPlain();
            }
        },
        
        showPlain: function() {
                this.target.hide();
                this.target.val('');
                this.displaySelect2(false);                
                $('#uniform-' + this.target[0].id).hide();
                this.$simpleEl.show();            
        },

        showOptions: function() {
                this.target.show();
                this.displaySelect2(true);
                $('#uniform-' + this.target[0].id).show();
                this.target.val('').trigger('change');
                this.target.find('option[value!=""]').remove();
                this.target.append(_.template(this.template, {contactphones: this.collection.models}));
                this.$simpleEl.hide();
                this.$simpleEl.val('');
        }
    });
});
