<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\EmailBundle\DataFixtures\ORM\AbstractEmailFixture;

class LoadEmailTemplates extends AbstractEmailFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'emails';
    }
}
