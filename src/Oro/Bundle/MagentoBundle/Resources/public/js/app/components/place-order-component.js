/* global ORO_ORDER_EMBED_API */
define(function(require) {
    'use strict';

    global.ORO_ORDER_EMBED_API = {};

    const BaseComponent = require('oroui/js/app/components/base/component');
    const PlaceOrderView = require('oromagento/js/app/views/place-order-view');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const widgetManager = require('oroui/js/widget-manager');
    const messenger = require('oroui/js/messenger');
    const __ = require('orotranslation/js/translator');

    /**
     * @export oromagento/js/app/components/place-order-component
     */
    const PlaceOrderComponent = BaseComponent.extend({
        optionNames: BaseComponent.prototype.optionNames.concat([
            'wid', 'errorMessage', 'cartSyncURL', 'customerSyncURL'
        ]),

        modalWidgetAlias: 'transaction-dialog',

        messageTemplate: _.template('<%= message %> <a href="<%= url %>" class="order-link"><%= urlLabel %></a> '),

        /**
         * @inheritDoc
         */
        constructor: function PlaceOrderComponent(options) {
            PlaceOrderComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            PlaceOrderComponent.__super__.initialize.call(this, options);

            // handle error
            if (this.errorMessage) {
                this._handleError();

                return;
            }

            this.view = new PlaceOrderView({
                el: options._sourceElement.find('>iframe')
            });

            widgetManager.getWidgetInstance(this.wid, this.onWidgetLoad.bind(this));

            this._setOroOrderEmbedApi();
        },

        onWidgetLoad: function(widget) {
            widget._showLoading();
            this.listenToOnce(this.view, 'frameLoaded', widget._hideLoading.bind(widget));
        },

        onOroOrderEmbedApiError: function() {
            messenger.notificationFlashMessage('error', __('oro.magento.external_error'));
            widgetManager.getWidgetInstanceByAlias(this.modalWidgetAlias, function(widget) {
                widget.remove();
            });
        },

        onOroOrderEmbedApiSuccess: function() {
            const performMessage = messenger.notificationFlashMessage(
                'warning', __('oro.magento.performing_synchronization')
            );

            widgetManager.getWidgetInstanceByAlias(this.modalWidgetAlias, function(widget) {
                widget.trigger('formSave');
                if (this.cartSyncURL) {
                    this._doCartSync(widget, performMessage);
                } else if (this.customerSyncURL) {
                    this._doCustomerSync(widget, performMessage);
                }
            }.bind(this));
        },

        _handleError: function() {
            messenger.notificationFlashMessage('error', this.errorMessage);
            widgetManager.getWidgetInstanceByAlias(this.modalWidgetAlias, function(widget) {
                widget.remove();
            });
        },

        _showMessage: function(data) {
            let message = data.message;

            if (data.statusType === 'success') {
                message = this.messageTemplate({
                    message: data.message,
                    url: data.url,
                    urlLabel: __('oro.magento.view_order')
                });
            }

            messenger.notificationFlashMessage(data.statusType, message);
        },

        _chooseMessage: function(data) {
            if (mediator.execute('isInAction')) {
                mediator.once('page:afterChange', function() {
                    this._showMessage(data);
                }.bind(this));
            } else {
                this._showMessage(data);
            }
        },

        _setOroOrderEmbedApi: function() {
            ORO_ORDER_EMBED_API.success = this.onOroOrderEmbedApiSuccess.bind(this);
            ORO_ORDER_EMBED_API.error = this.onOroOrderEmbedApiError.bind(this);
        },

        _doCustomerSync: function(widget, performMessage) {
            $.ajax({
                method: 'post',
                dataType: 'json',
                url: this.customerSyncURL,
                beforeSend: function() {
                    widget.remove();
                },
                success: function(data) {
                    mediator.trigger('datagrid:doReset:magento-customer-orders-widget-grid');
                    this._chooseMessage(data);
                }.bind(this),
                errorHandlerMessage: __('oro.magento.external_error')
            }).always(performMessage.close);
        },

        _doCartSync: function(widget, performMessage) {
            $.ajax({
                method: 'post',
                dataType: 'json',
                url: this.cartSyncURL,
                beforeSend: function() {
                    widget.remove();
                    mediator.execute('showLoading');
                },
                success: function(data) {
                    if (mediator.execute('isInAction')) {
                        mediator.once('page:afterChange', function() {
                            this._showMessage(data);
                        }.bind(this));
                    } else {
                        mediator.trigger('datagrid:doReset:magento-customer-orders-widget-grid');
                        this._showMessage(data);
                        mediator.execute('refreshPage');
                    }
                }.bind(this)
            }).always(performMessage.close);
        }
    });

    return PlaceOrderComponent;
});
