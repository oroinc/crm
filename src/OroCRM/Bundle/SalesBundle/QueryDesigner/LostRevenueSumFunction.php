<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class LostRevenueSumFunction extends AbstractRevenueSumFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getStatus()
    {
        return 'lost';
    }
}
