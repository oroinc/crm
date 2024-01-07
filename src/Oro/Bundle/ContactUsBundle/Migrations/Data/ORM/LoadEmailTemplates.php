<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

/**
 * Loads email templates.
 */
class LoadEmailTemplates extends AbstractEmailFixture
{
    /**
     * {@inheritDoc}
     */
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroContactUsBundle/Migrations/Data/ORM/data/emails/contact_request');
    }
}
