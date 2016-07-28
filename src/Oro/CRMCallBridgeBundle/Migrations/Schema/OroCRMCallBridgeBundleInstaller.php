<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\OroCRMCallBridgeBundle;

class OroCRMCallBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
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
        if (!$this->container->hasParameter('installed') || !$this->container->getParameter('installed')) {
            OroCRMCallBridgeBundle::addCallActivityRelations($schema, $this->activityExtension);
        }
    }
}
