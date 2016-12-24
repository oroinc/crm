<?php

namespace Oro\Bundle\MagentoBundle\Provider;

class TrackingCustomerIdentificationEvents
{
    const EVENT_REGISTRATION_FINISHED = 'registration';
    const EVENT_CART_ITEM_ADDED       = 'cart item added';
    const EVENT_CHECKOUT_STARTED      = 'user entered checkout';
    const EVENT_ORDER_PLACE_SUCCESS   = 'order successfully placed';
    const EVENT_ORDER_PLACED          = 'order placed';
    const EVENT_CUSTOMER_LOGIN        = 'user logged in';
    const EVENT_CUSTOMER_LOGOUT       = 'user logged out';
    const EVENT_VISIT                 = 'visit';
}
