var Oro = Oro || {};

/**
 * OroApp extension of Bootstrap Modal wrapper for use with Backbone.
 */
(function($, _, Backbone) {
    Oro.BootstrapModal = Backbone.BootstrapModal.extend({
        /** @property {Object} */
        template: _.template('\
            <% if (title) { %>\
              <div class="modal-header">\
                <% if (allowCancel) { %>\
                  <a class="close">×</a>\
                <% } %>\
                <h3><%= title %></h3>\
              </div>\
            <% } %>\
            <div class="modal-body"><%= content %></div>\
            <div class="modal-footer">\
              <a href="#" class="btn ok btn-danger"><%= okText %></a>\
            </div>\
        '),

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            options = _.extend({
                template: this.template
            }, options);
            Backbone.BootstrapModal.prototype.initialize.apply(this, arguments);
        }
    });
})(jQuery, _, Backbone);
/*
*  add to modal-footer if you need button "Cancel"
 <% if (allowCancel) { %>\
 <% if (cancelText) { %>\
 <a href="#" class="btn cancel"><%= cancelText %></a>\
 <% } %>\
 <% } %>\
 */