<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\DataFixtures\Demo\ORM;

use Oro\Bundle\EmailBundle\Migrations\DataFixtures\ORM\AbstractEmailFixture;

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
