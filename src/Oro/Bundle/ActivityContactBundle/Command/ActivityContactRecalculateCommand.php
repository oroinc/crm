<?php
declare(strict_types=1);

namespace Oro\Bundle\ActivityContactBundle\Command;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\EventListener\ActivityListener;
use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\Repository\ActivityListRepository;
use Oro\Bundle\ActivityListBundle\Filter\ActivityListFilterHelper;
use Oro\Bundle\ActivityListBundle\Tools\ActivityListEntityConfigDumperExtension;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Component\Log\OutputLogger;
use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Recalculates counters and last contact date for contact activities.
 */
class ActivityContactRecalculateCommand extends Command
{
    private const STATUS_SUCCESS = 0;
    private const BATCH_SIZE     = 100;

    /** @var string */
    protected static $defaultName = 'oro:activity-contact:recalculate';

    protected OroEntityManager $em;
    protected ActivityListRepository $activityListRepository;
    private ManagerRegistry $registry;
    private ConfigProvider $activityConfigProvider;
    private ConfigProvider $extendConfigProvider;
    private ActivityListener $activityListener;
    private ActivityListFilterHelper $activityListFilterHelper;
    private ActivityContactProvider $activityContactProvider;

    public function __construct(
        ManagerRegistry $registry,
        ConfigProvider $activityConfigProvider,
        ConfigProvider $extendConfigProvider,
        ActivityListener $activityListener,
        ActivityListFilterHelper $activityListFilterHelper,
        ActivityContactProvider $activityContactProvider
    ) {
        parent::__construct();

        $this->registry = $registry;
        $this->activityConfigProvider = $activityConfigProvider;
        $this->extendConfigProvider = $extendConfigProvider;
        $this->activityListener = $activityListener;
        $this->activityListFilterHelper = $activityListFilterHelper;
        $this->activityContactProvider = $activityContactProvider;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this->setDescription('Recalculates counters and last contact date for contact activities.');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);

        return $this->recalculate($logger);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function recalculate(AbstractLogger $logger): int
    {
        $logger->info('Recalculating contacting activities...');
        $logger->info(sprintf('<info>Processing started at %s</info>', date('Y-m-d H:i:s')));

        $contactingActivityClasses = $this->activityContactProvider->getSupportedActivityClasses();

        $entityConfigsWithApplicableActivities = $this->activityConfigProvider->filter(
            function (ConfigInterface $entity) use ($contactingActivityClasses) {
                return
                    $entity->get('activities')
                    && array_intersect($contactingActivityClasses, $entity->get('activities')) &&
                    $this->extendConfigProvider->getConfig($entity->getId()->getClassName())->is('is_extend');
            }
        );

        if ($entityConfigsWithApplicableActivities) {
            $logger->info(
                sprintf(
                    '<comment>Total found %d entities with enabled contacting activities</comment>',
                    count($entityConfigsWithApplicableActivities)
                )
            );
            $this->em = $this->registry->getManager();
            $this->activityListRepository = $this->em->getRepository(ActivityList::class);

            foreach ($entityConfigsWithApplicableActivities as $activityScopeConfig) {
                $entityClassName = $activityScopeConfig->getId()->getClassName();
                if (TargetExcludeList::isExcluded($entityClassName)) {
                    continue;
                }
                $offset          = 0;
                $startTimestamp  = time();
                $allRecordIds    = $this->getTargetIds($entityClassName);
                $this->resetRecordsWithoutActivities($entityClassName, $allRecordIds);
                while ($allRecords = $this->getRecordsToRecalculate($entityClassName, $allRecordIds, $offset)) {
                    $needsFlush = false;
                    foreach ($allRecords as $record) {
                        $this->resetRecordStatistic($record);
                        /** @var QueryBuilder $qb */
                        $qb = $this->activityListRepository->getBaseActivityListQueryBuilder(
                            $entityClassName,
                            $record->getId()
                        );
                        $this->activityListFilterHelper->addFiltersToQuery(
                            $qb,
                            ['activityType' => ['value' => $contactingActivityClasses]]
                        );

                        /** @var ActivityList[] $activities */
                        $activities = $qb->getQuery()->getResult();
                        if ($activities) {
                            foreach ($activities as $activityListItem) {
                                /** @var object $activity */
                                $activity = $this->em->getRepository($activityListItem->getRelatedActivityClass())
                                    ->find($activityListItem->getRelatedActivityId());

                                $this->activityListener->onAddActivity(new ActivityEvent($activity, $record));
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

    protected function resetRecordsWithoutActivities(string $entityClassName, array $recordIdsWithActivities): void
    {
        $offset = 0;
        while ($records = $this->getRecordsToReset($entityClassName, $recordIdsWithActivities, $offset)) {
            array_map([$this, 'resetRecordStatistic'], $records);
            $this->em->flush();
            $this->em->clear();
            $offset += self::BATCH_SIZE;
        }
    }

    protected function resetRecordStatistic(object $entity): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $accessor->setValue($entity, ActivityScope::CONTACT_COUNT, 0);
        $accessor->setValue($entity, ActivityScope::CONTACT_COUNT_IN, 0);
        $accessor->setValue($entity, ActivityScope::CONTACT_COUNT_OUT, 0);
        $accessor->setValue($entity, ActivityScope::LAST_CONTACT_DATE, null);
        $accessor->setValue($entity, ActivityScope::LAST_CONTACT_DATE_IN, null);
        $accessor->setValue($entity, ActivityScope::LAST_CONTACT_DATE_OUT, null);
    }

    protected function getRecordsToRecalculate(string $entityClassName, array $ids, int $offset): array
    {
        $entityRepository = $this->em->getRepository($entityClassName);

        return $entityRepository->findBy(['id' => $ids], ['id' => 'ASC'], self::BATCH_SIZE, $offset);
    }

    protected function getRecordsToReset(string $entityClassName, array $excludedIds, int $offset): array
    {
        $qb = $this->em->getRepository($entityClassName)->createQueryBuilder('e');

        if ($excludedIds) {
            $qb->andWhere($qb->expr()->notIn('e.id', ':excludedIds'));
            $qb->setParameter('excludedIds', $excludedIds);
        }

        return $qb->setMaxResults(static::BATCH_SIZE)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns entity ids of records that have associated contacting activities
     */
    protected function getTargetIds(string $className): array
    {
        $contactingActivityClasses = $this->activityContactProvider->getSupportedActivityClasses();

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

    protected function getAssociationName(string $className): string
    {
        return ExtendHelper::buildAssociationName(
            $className,
            ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND
        );
    }
}
