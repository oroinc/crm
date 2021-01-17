<?php

namespace Oro\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Inflector\Inflector;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EmailBundle\Tools\EmailHolderHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Provides the direction information for Email activity.
 */
class EmailDirectionProvider implements DirectionProviderInterface
{
    /** @var ConfigProvider */
    protected $configProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EmailHolderHelper */
    protected $emailHolderHelper;
    private Inflector $inflector;

    public function __construct(
        ConfigProvider $configProvider,
        DoctrineHelper $doctrineHelper,
        EmailHolderHelper $emailHolderHelper,
        Inflector $inflector
    ) {
        $this->configProvider = $configProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->emailHolderHelper = $emailHolderHelper;
        $this->inflector = $inflector;
    }

    /**
     * {@inheritdoc}
     * @param Email $activity
     */
    public function getDirection($activity, $target)
    {
        // check if target is entity created from admin part
        if (!$target instanceof EmailHolderInterface) {
            $metadata = $this->doctrineHelper->getEntityMetadata($target);
            $columns = $metadata->getColumnNames();
            $targetClass = ClassUtils::getClass($target);
            foreach ($columns as $column) {
                // check only columns with 'contact_information'
                if ($this->isEmailType($targetClass, $column)) {
                    $getMethodName = 'get' . $this->inflector->classify($column);
                    if ($activity->getFromEmailAddress()->getEmail() === $target->$getMethodName()) {
                        return DirectionProviderInterface::DIRECTION_OUTGOING;
                    }
                    foreach ($activity->getTo() as $recipient) {
                        if ($recipient->getEmailAddress()->getEmail() === $target->$getMethodName()) {
                            return DirectionProviderInterface::DIRECTION_INCOMING;
                        }
                    }
                }
            }

            return DirectionProviderInterface::DIRECTION_UNKNOWN;
        }

        if ($activity->getFromEmailAddress()->getEmail() === $target->getEmail()) {
            return DirectionProviderInterface::DIRECTION_OUTGOING;
        }

        return DirectionProviderInterface::DIRECTION_INCOMING;
    }

    /**
     * @param string      $className
     * @param string|null $column
     *
     * @return bool
     */
    protected function isEmailType($className, $column)
    {
        if (!$this->configProvider->hasConfig($className, $column)) {
            return false;
        }

        $fieldConfiguration = $this->configProvider->getConfig($className, $column);

        return $fieldConfiguration->get('contact_information') === self::CONTACT_INFORMATION_SCOPE_EMAIL;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectionChanged($changeSet = [])
    {
        /**
         * For emails direction never can be changed at all.
         */
        return false;
    }

    /**
     * {@inheritdoc}
     * @param Email $activity
     */
    public function getDate($activity)
    {
        return $activity->getSentAt() ? : new \DateTime('now', new \DateTimeZone('UTC'));
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
     * @return Email
     */
    protected function getLastActivity(EntityManager $em, $target, $skipId = null, $direction = null)
    {
        $targetClass = ClassUtils::getClass($target);
        $qb = $em->getRepository(Email::class)
            ->createQueryBuilder('email')
            ->select('email')
            ->innerJoin(
                sprintf('email.%s', ExtendHelper::buildAssociationName($targetClass, ActivityScope::ASSOCIATION_KIND)),
                'target'
            )
            ->andWhere('target = :target')
            ->orderBy('email.sentAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('target', $target);

        if ($skipId) {
            $qb->andWhere('email.id <> :skipId')
                ->setParameter('skipId', $skipId);
        }

        if ($direction && $target instanceof EmailHolderInterface) {
            $operator = '!=';
            if ($direction === DirectionProviderInterface::DIRECTION_OUTGOING) {
                $operator = '=';
            }

            $qb->join('email.fromEmailAddress', 'fromEmailAddress')
                ->andWhere('fromEmailAddress.email ' . $operator . ':email')
                ->setParameter('email', $this->getTargetEmail($target));
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param object $target
     * @return string
     */
    protected function getTargetEmail($target)
    {
        return $this->emailHolderHelper->getEmail($target);
    }
}
