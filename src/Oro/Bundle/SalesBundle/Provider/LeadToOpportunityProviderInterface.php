<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

interface LeadToOpportunityProviderInterface
{
    /**
     * @param Lead $lead
     * @param bool $isGetRequest
     *
     * @return Opportunity
     */
    public function prepareOpportunityForForm(Lead $lead, $isGetRequest = true);

    /**
     * @param Opportunity $opportunity
     * @param callable    $errorMessageCallback
     *
     * @return bool
     */
    public function saveOpportunity(Opportunity $opportunity, callable $errorMessageCallback);
}
