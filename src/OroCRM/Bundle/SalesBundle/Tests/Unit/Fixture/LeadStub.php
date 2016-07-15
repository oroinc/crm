<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Fixture;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadStub extends Lead
{
    private $status = null;

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
