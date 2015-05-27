<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migrations\Schema\v1_0;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class AddActivityContactGroup implements Migration, ContainerAwareInterface
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
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addActivityContactGroup($this->container, $queries);
    }

    /**
     * @param ContainerInterface $container
     * @param QueryBag           $queries
     */
    public static function addActivityContactGroup(ContainerInterface $container, QueryBag $queries)
    {
        /** @var Registry $entities */
        $doctrineRegistry = $container->get('doctrine');

        /** @var ActivityContactProvider $activityContactProvider */
        $activityContactProvider = $container->get('orocrm_activity_contact.provider');
        $contactingActivityClasses = $activityContactProvider->getSupportedActivityClasses();

        /** @var EntityConfigModel[] $entities */
        $entities = $doctrineRegistry->getManager()->getRepository('OroEntityConfigBundle:EntityConfigModel')
            ->findEntitiesByClassNames($contactingActivityClasses);

        foreach ($entities as $entity) {
            $entityGrouping = $entity->toArray('grouping');
            $entityGroups   = isset($entityGrouping['groups']) ? $entityGrouping['groups'] : [];
            if ($entityGroups && !in_array(ActivityScope::GROUP_ACTIVITY_CONTACT, $entityGroups)) {
                $queries->addQuery(
                    new UpdateEntityConfigEntityValueQuery(
                        $entity->getClassName(),
                        'grouping',
                        'groups',
                        array_merge($entityGroups, [ActivityScope::GROUP_ACTIVITY_CONTACT])
                    )
                );
            }
        }
    }
}
