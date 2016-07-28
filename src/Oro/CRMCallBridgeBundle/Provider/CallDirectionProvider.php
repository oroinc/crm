<?php

namespace Oro\CRMCallBridgeBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallDirectionProvider implements DirectionProviderInterface
{
    /** @var ActivityManager */
    protected $activityManager;

    /**
     * @param ActivityManager $activityManager
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->activityManager = $activityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedClass()
    {
        return 'OroCRM\Bundle\CallBundle\Entity\Call';
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection($activity, $target)
    {
        /** @var $activity Call */
        return $activity->getDirection()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectionChanged($changeSet = [])
    {
        if (!empty($changeSet)) {
            return in_array('direction', array_keys($changeSet));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate($activity)
    {
        /** @var $activity Call */
        return $activity->getCallDateTime() ? : new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function getLastActivitiesDateForTarget(EntityManager $em, $target, $direction, $skipId = null)
    {
        $result         = [];
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
        if (!$this->activityManager->hasActivityAssociation(
            ClassUtils::getClass($target),
            $this->getSupportedClass()
        )) {
            return null;
        }

        $qb = $em->getRepository('OroCRM\Bundle\CallBundle\Entity\Call')
            ->createQueryBuilder('call')
            ->select('call')
            ->innerJoin(
                sprintf(
                    'call.%s',
                    ExtendHelper::buildAssociationName(ClassUtils::getClass($target), ActivityScope::ASSOCIATION_KIND)
                ),
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
