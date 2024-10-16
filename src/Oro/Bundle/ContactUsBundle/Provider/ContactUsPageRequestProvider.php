<?php

namespace Oro\Bundle\ContactUsBundle\Provider;

use Oro\Bundle\PlatformBundle\Provider\AbstractPageRequestProvider;

/**
 * Provide list of contact us page requests.
 */
class ContactUsPageRequestProvider extends AbstractPageRequestProvider
{
    #[\Override]
    public function getRequests(): array
    {
        return [
            $this->createRequest('GET', 'oro_contactus_bridge_contact_us_page')
        ];
    }
}
