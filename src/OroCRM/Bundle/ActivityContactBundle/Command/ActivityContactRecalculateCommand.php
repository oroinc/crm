<?php

namespace OroCRM\Bundle\ActivityContactBundle\Command;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Psr\Log\AbstractLogger;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Component\PropertyAccess\PropertyAccessor;
use Oro\Component\Log\OutputLogger;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\Repository\ActivityListRepository;
use Oro\Bundle\ActivityListBundle\Filter\ActivityListFilterHelper;
use Oro\Bundle\ActivityListBundle\Tools\ActivityListEntityConfigDumperExtension;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\EventListener\ActivityListener;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityContactRecalculateCommand extends ContainerAwareCommand
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:activity-contact:recalculate';
    const BATCH_SIZE     = 100;

    /** @var OroEntityManager $em */
    protected $em;

    /** @var ActivityListRepository $activityListRepository */
    protected $activityListRepository;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Recalculate contacting activities');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);

        $this->recalculate($logger);
    }

    /**
     * @param AbstractLogger $logger
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function recalculate(AbstractLogger $logger)
    {
        $logger->notice('Recalculating contacting activities...');
        $logger->info(sprintf('<info>Processing started at %s</info>', date('Y-m-d H:i:s')));

        /** @var ConfigProvider $activityConfigProvider */
        $activityConfigProvider = $this->getContainer()->get('oro_entity_config.provider.activity');

        /** @var ActivityContactProvider $activityContactProvider */
        $activityContactProvider   = $this->getContainer()->get('orocrm_activity_contact.provider');
        $contactingActivityClasses = $activityContactProvider->getSupportedActivityClasses();

        $entityConfigsWithApplicableActivities = $activityConfigProvider->filter(
            function (ConfigInterface $entity) use ($contactingActivityClasses) {
                return
                    $entity->get('activities')
                    && array_intersect($contactingActivityClasses, $entity->get('activities'));
            }
        );

        if ($entityConfigsWithApplicableActivities) {
            $logger->info(
                sprintf(
                    '<comment>Total found %d entities with enabled contacting activities</comment>',
                    count($entityConfigsWithApplicableActivities)
                )
            );

            $this->em                     = $this->getContainer()->get('doctrine')->getManager();
            $this->activityListRepository = $this->em->getRepository(ActivityList::ENTITY_NAME);

            /** @var ActivityListener $activityListener */
            $activityListener = $this->getContainer()->get('orocrm_activity_contact.listener.activity_listener');

            /** @var ActivityListFilterHelper $activityListHelper */
            $activityListHelper = $this->getContainer()->get('oro_activity_list.filter.helper');

            /** @var PropertyAccessor $accessor */
            $accessor = PropertyAccess::createPropertyAccessor();

            foreach ($entityConfigsWithApplicableActivities as $activityScopeConfig) {
                $entityClassName  = $activityScopeConfig->getId()->getClassName();
                $entityRepository = $this->em->getRepository($entityClassName);
                $offset           = 0;
                $startTimestamp   = time();
                $allRecordIds     = $this->getTargetIds($entityClassName);

                while ($allRecords = $entityRepository->findBy(
                    ['id' => $allRecordIds],
                    ['id' => 'ASC'],
                    self::BATCH_SIZE,
                    $offset
                )) {
                    $needsFlush = false;
                    foreach ($allRecords as $record) {
                        /** Reset record statistics. */
                        $accessor->setValue($record, ActivityScope::CONTACT_COUNT, 0);
                        $accessor->setValue($record, ActivityScope::CONTACT_COUNT_IN, 0);
                        $accessor->setValue($record, ActivityScope::CONTACT_COUNT_OUT, 0);
                        $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE, null);
                        $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE_IN, null);
                        $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE_OUT, null);

                        /** @var QueryBuilder $qb */
                        $qb = $this->activityListRepository->getBaseActivityListQueryBuilder(
                            $entityClassName,
                            $record->getId()
                        );
                        $activityListHelper->addFiltersToQuery(
                            $qb,
                            [
                                'activityType' => [
                                    'value' => $contactingActivityClasses
                                ]
                            ]
                        );

                        /** @var ActivityList[] $activities */
                        $activities = $qb->getQuery()->getResult();
                        if ($activities) {
                            foreach ($activities as $activityListItem) {
                                /** @var object $activity */
                                $activity = $this->em->getRepository($activityListItem->getRelatedActivityClass())
                                    ->find($activityListItem->getRelatedActivityId());

                                $event = new ActivityEvent($activity, $record);
                                $activityListener->onAddActivity($event);
                            }
                            $this->em->persist($record);
                            $needsFlush = true;
                        }
                    }

                    if ($needsFlush) {
                        $this->em->flush();
                    }

                    $this->em->clear();

                    $offset += self::BATCH_SIZE;
                }

                $endTimestamp = time();
                $logger->info(
                    sprintf(
                        'Entity "%s", %d records processed (<comment>%d sec.</comment>).',
                        $entityClassName,
                        count($allRecordIds),
                        ($endTimestamp - $startTimestamp)
                    )
                );
            }
        }

        $logger->info(sprintf('<info>Processing finished at %s</info>', date('Y-m-d H:i:s')));

        return self::STATUS_SUCCESS;
    }

    /**
     * Returns entity ids of records that have associated contacting activities
     *
     * @param string $className Target entity class name
     * @return array|int
     */
    protected function getTargetIds($className)
    {
        /** @var ActivityContactProvider $activityContactProvider */
        $activityContactProvider   = $this->getContainer()->get('orocrm_activity_contact.provider');
        $contactingActivityClasses = $activityContactProvider->getSupportedActivityClasses();

        // we need try/catch here to avoid crash on non existing entity relation
        try {
            $result = $this->activityListRepository->createQueryBuilder('list')
                ->select('r.id')
                ->distinct(true)
                ->join('list.' . $this->getAssociationName($className), 'r')
                ->where('list.relatedActivityClass in (:applicableClasses)')
                ->setParameter('applicableClasses', $contactingActivityClasses)
                ->getQuery()
                ->getScalarResult();

            $result = array_map('current', $result);
        } catch (\Exception $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * Get Association name
     *
     * @param string $className
     *
     * @return string
     */
    protected function getAssociationName($className)
    {
        return ExtendHelper::buildAssociationName(
            $className,
            ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND
        );
    }
}
