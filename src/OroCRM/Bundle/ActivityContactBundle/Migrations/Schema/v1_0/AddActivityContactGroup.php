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
        /** @var Registry $entities */
        $doctrineRegistry = $this->container->get('doctrine');

        /** @var EntityConfigModel[] $entities */
        $entities = $doctrineRegistry->getManager()->getRepository('OroEntityConfigBundle:EntityConfigModel')
            ->findEntitiesByClassNames(ActivityScope::$contactingActivityClasses);

        foreach ($entities as $entity) {
            if (!in_array(ActivityScope::GROUP_ACTIVITY_CONTACT, $entity->toArray('grouping')['groups'])) {
                /*$value = $doctrineRegistry->getConnection()->convertToDatabaseValue(
                    array_merge($entity->toArray('grouping')['groups'], [ActivityScope::GROUP_ACTIVITY_CONTACT]),
                    Type::TARRAY
                );*/

                $value = array_merge($entity->toArray('grouping')['groups'], [ActivityScope::GROUP_ACTIVITY_CONTACT]);

                $queries->addQuery(
                    new UpdateEntityConfigEntityValueQuery(
                        $entity->getClassName(),
                        'grouping',
                        'groups',
                        $value
                    )
                );
            }
        }

    }
}
