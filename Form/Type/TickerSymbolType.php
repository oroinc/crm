<?php
namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class TickerSymbolType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_ticker_symbol';
    }
}
