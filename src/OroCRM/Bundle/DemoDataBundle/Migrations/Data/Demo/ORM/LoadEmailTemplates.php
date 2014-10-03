<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadEmailTemplates extends AbstractEmailFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroCRMDemoDataBundle/Migrations/Data/Demo/ORM/emails');
    }
}
