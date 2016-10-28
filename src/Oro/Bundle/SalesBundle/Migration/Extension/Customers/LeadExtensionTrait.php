<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

trait LeadExtensionTrait
{
    /** @var LeadExtension */
    protected $leadExtension;

    /**
     * @param LeadExtension $leadExtension
     */
    public function setLeadExtension(LeadExtension $leadExtension)
    {
        $this->leadExtension = $leadExtension;
    }
}
