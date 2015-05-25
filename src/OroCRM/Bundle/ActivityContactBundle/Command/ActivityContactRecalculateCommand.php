<?php

namespace OroCRM\Bundle\ActivityContactBundle\Command;

use Doctrine\ORM\QueryBuilder;

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
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\EventListener\ActivityListener;

class ActivityContactRecalculateCommand extends ContainerAwareCommand
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:activity-contact:recalculate';

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

        $logger->notice('Recalculating contacting activities...');
        $logger->info(sprintf('<info>Processing started at %s</info>', date('Y-m-d H:i:s')));

        /** @var ConfigProvider $activityConfigProvider */
        $activityConfigProvider = $this->getContainer()->get('oro_entity_config.provider.activity');

        $entityConfigsWithApplicableActivities = $activityConfigProvider->filter(
            function (ConfigInterface $entity) {
                return
                    $entity->get('activities')
                    && array_intersect(ActivityScope::$contactingActivityClasses, $entity->get('activities'));
            }
        );

        if ($entityConfigsWithApplicableActivities) {
            $logger->info(
                sprintf(
                    '<comment>Total found %d entities with enabled contacting activities</comment>',
                    count($entityConfigsWithApplicableActivities)
                )
            );

            /** @var OroEntityManager $em */
            $em = $this->getContainer()->get('doctrine')->getManager();

            /** @var ActivityListRepository $activityListRepo */
            $activityListRepo = $em->getRepository(ActivityList::ENTITY_NAME);

            /** @var ActivityListener $activityListener */
            $activityListener = $this->getContainer()->get('orocrm_activity_contact.listener.activity_listener');

            /** @var ActivityListFilterHelper $activityListHelper */
            $activityListHelper = $this->getContainer()->get('oro_activity_list.filter.helper');

            /** @var PropertyAccessor $accessor */
            $accessor = PropertyAccess::createPropertyAccessor();

            foreach ($entityConfigsWithApplicableActivities as $activityScopeConfig) {
                $entityClassName  = $activityScopeConfig->getId()->getClassName();
                $entityRepository = $em->getRepository($entityClassName);
                $allRecords       = $entityRepository->findAll();
                $startTimestamp   = time();
                foreach ($allRecords as $record) {
                    /**
                     * Reset record statistics.
                     */
                    $accessor->setValue($record, ActivityScope::CONTACT_COUNT, 0);
                    $accessor->setValue($record, ActivityScope::CONTACT_COUNT_IN, 0);
                    $accessor->setValue($record, ActivityScope::CONTACT_COUNT_OUT, 0);
                    $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE, null);
                    $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE_IN, null);
                    $accessor->setValue($record, ActivityScope::LAST_CONTACT_DATE_OUT, null);

                    /** @var QueryBuilder $qb */
                    $qb = $activityListRepo->getBaseActivityListQueryBuilder($entityClassName, $record->getId());
                    $activityListHelper->addFiltersToQuery(
                        $qb,
                        [
                            'activityType' => [
                                'value' => ActivityScope::$contactingActivityClasses
                            ]
                        ]
                    );

                    /** @var ActivityList[] $activities */
                    $activities = $qb->getQuery()->getResult();
                    foreach ($activities as $activityListItem) {
                        /** @var object $activity */
                        $activity = $em->getRepository($activityListItem->getRelatedActivityClass())
                            ->find($activityListItem->getRelatedActivityId());

                        $event = new ActivityEvent($activity, $record);
                        $activityListener->onAddActivity($event);
                    }
                    $em->persist($record);
                }

                $em->flush();

                $endTimestamp = time();
                $logger->info(
                    sprintf(
                        'Entity "%s", %d records processed (<comment>%d sec.</comment>).',
                        $entityClassName,
                        count($allRecords),
                        ($endTimestamp - $startTimestamp)
                    )
                );
            }
        }

        $logger->info(sprintf('<info>Processing finished at %s</info>', date('Y-m-d H:i:s')));

        return self::STATUS_SUCCESS;
    }
}
