<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadEntityStub extends Lead
{
    /**
     * @var object
     */
    protected $source;

    /**
     * @var object
     */
    protected $status;

    /**
     * @return object
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param object $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param object $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }
}
