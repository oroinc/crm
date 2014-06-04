<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class InProgressRevenueSumFunction extends AbstractRevenueSumFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getStatus()
    {
        return 'in_progress';
    }
}
