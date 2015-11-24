<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Inflector\Inflector;

use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EmailBundle\Tools\EmailHolderHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

/**
 * Class EmailDirectionProvider
 * @package OroCRM\Bundle\ActivityContactBundle\Provider
 */
class EmailDirectionProvider implements DirectionProviderInterface
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param ConfigProvider $configProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        ConfigProvider $configProvider,
        DoctrineHelper $doctrineHelper,
        EmailHolderHelper $emailHolderHelper
    ) {
        $this->configProvider = $configProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->emailHolderHelper = $emailHolderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedClass()
    {
        return 'Oro\Bundle\EmailBundle\Entity\Email';
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection($activity, $target)
    {
        //check if target is entity created from admin part
        if (!$target instanceof EmailHolderInterface) {
            $metadata = $this->doctrineHelper->getEntityMetadata($target);
            $columns = $metadata->getColumnNames();
            $className = get_class($target);

            foreach ($columns as $column) {
                //check only columns with 'contact_information'
                if ($this->isEmailType($className, $column)) {
                    $getMethodName = "get" . Inflector::classify($column);
                    /** @var $activity Email */
                    if ($activity->getFromEmailAddress()->getEmail() === $target->$getMethodName()) {
                        return DirectionProviderInterface::DIRECTION_OUTGOING;
                    } else {
                        foreach ($activity->getTo() as $recipient) {
                            if ($recipient->getEmailAddress()->getEmail() === $target->$getMethodName()) {
                                return DirectionProviderInterface::DIRECTION_INCOMING;
                            }
                        }
                    }
                }
            }

            return DirectionProviderInterface::DIRECTION_UNKNOWN;
        }

        /** @var $activity Email */
        /** @var $target EmailHolderInterface */
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
        if ($this->configProvider->hasConfig($className, $column)) {
            $fieldConfiguration = $this->configProvider->getConfig($className, $column);
            $type = $fieldConfiguration->get('contact_information');

            return $type == ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL;
        }

        return false;
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
     */
    public function getDate($activity)
    {
        /** @var $activity Email */
        return $activity->getSentAt() ? : new \DateTime('now', new \DateTimeZone('UTC'));
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
     * @return Email
     */
    protected function getLastActivity(EntityManager $em, $target, $skipId = null, $direction = null)
    {
        $qb = $em->getRepository('Oro\Bundle\EmailBundle\Entity\Email')
            ->createQueryBuilder('email')
            ->select('email')
            ->innerJoin(
                sprintf(
                    'email.%s',
                    ExtendHelper::buildAssociationName(ClassUtils::getClass($target), ActivityScope::ASSOCIATION_KIND)
                ),
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
