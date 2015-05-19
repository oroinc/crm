<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\ActivityContactBundle\Migrations\Schema\v1_0\AddActivityContactGroup;

class OroCRMActivityContactBundleInstaller implements Installation, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        AddActivityContactGroup::addActivityContactGroup($this->container, $queries);
    }
}
