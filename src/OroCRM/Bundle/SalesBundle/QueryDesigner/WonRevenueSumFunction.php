<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class WonRevenueSumFunction extends AbstractRevenueSumFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getStatus()
    {
        return 'won';
    }
}
