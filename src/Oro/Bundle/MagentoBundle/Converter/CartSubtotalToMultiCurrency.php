<?php
namespace Oro\Bundle\MagentoBundle\Converter;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\MagentoBundle\Entity\Cart;

class CartSubtotalToMultiCurrency
{
    public function convertCartTotalToMultiCurrency(Cart $cart)
    {
        $currency = $cart->getQuoteCurrencyCode();
        $multiCurrencyObject = MultiCurrency::create($cart->getSubTotal(), $currency);
        return $multiCurrencyObject;
    }
}
