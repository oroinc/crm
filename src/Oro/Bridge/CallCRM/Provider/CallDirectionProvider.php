<?php

namespace Oro\Bridge\CallCRM\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Provides the direction information for Call activity.
 */
class CallDirectionProvider implements DirectionProviderInterface
{
    /** @var ActivityManager */
    protected $activityManager;

    public function __construct(ActivityManager $activityManager)
    {
        $this->activityManager = $activityManager;
    }

    /**
     * {@inheritdoc}
     * @param Call $activity
     */
    public function getDirection($activity, $target)
    {
        return $activity->getDirection()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectionChanged($changeSet = [])
    {
        return array_key_exists('direction', $changeSet);
    }

    /**
     * {@inheritdoc}
     * @param Call $activity
     */
    public function getDate($activity)
    {
        return $activity->getCallDateTime() ? : new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function getLastActivitiesDateForTarget(EntityManager $em, $target, $direction, $skipId = null)
    {
        $result = [];
        $resultActivity = $this->getLastActivity($em, $target, $skipId);
        if ($resultActivity) {
            $result['all'] = $this->getDate($resultActivity);
            if ($this->getDirection($resultActivity, $target) !== $direction) {
                $resultActivity = $this->getLastActivity($em, $target, $skipId, $direction);
                if ($resultActivity) {
                    $result['direction'] = $this->getDate($resultActivity);
                } else {
                    $result['direction'] = null;
                }
            } else {
                $result['direction'] = $result['all'];
            }
        }

        return $result;
    }

    /**
     * @param EntityManager $em
     * @param object        $target
     * @param integer       $skipId
     * @param string        $direction
     *
     * @return Call
     */
    protected function getLastActivity(EntityManager $em, $target, $skipId, $direction = null)
    {
        $targetClass = ClassUtils::getClass($target);
        if (!$this->activityManager->hasActivityAssociation($targetClass, Call::class)) {
            return null;
        }

        $qb = $em->getRepository(Call::class)
            ->createQueryBuilder('call')
            ->select('call')
            ->innerJoin(
                sprintf('call.%s', ExtendHelper::buildAssociationName($targetClass, ActivityScope::ASSOCIATION_KIND)),
                'target'
            )
            ->andWhere('target = :target')
            ->orderBy('call.callDateTime', 'DESC')
            ->setMaxResults(1)
            ->setParameter('target', $target->getId());
        if ($skipId) {
            $qb->andWhere('call.id <> :skipId')
                ->setParameter('skipId', $skipId);
        }

        if ($direction) {
            $qb->join('call.direction', 'direction')
                ->andWhere('direction.name = :direction')
                ->setParameter('direction', $direction);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
