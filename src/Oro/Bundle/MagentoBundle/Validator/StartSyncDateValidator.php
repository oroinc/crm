<?php

namespace Oro\Bundle\MagentoBundle\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Validator\Constraints\StartSyncDateConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StartSyncDateValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param \DateTime $value
     * @param StartSyncDateConstraint $constraint
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \DateTime) {
            return;
        }

        $integration = $this->context->getRoot()->getData();
        if (!$integration instanceof Channel) {
            return;
        }

        $transport = $integration->getTransport();
        if (!$transport instanceof MagentoTransport) {
            return;
        }

        if (!$transport->getId()) {
            return;
        }

        $oldValue = $this->getTransportSyncStartDate($transport->getId());
        if (!$oldValue) {
            return;
        }

        if ($value > $oldValue) {
            $this->context->buildViolation($constraint->message)
                ->atPath($this->context->getPropertyPath())
                ->addViolation();
        }
    }

    /**
     * @param int $transportId
     *
     * @return \DateTime
     */
    protected function getTransportSyncStartDate($transportId)
    {
        $qb = $this->getRepository()->createQueryBuilder('t');

        $qb
            ->select('t.syncStartDate')
            ->where($qb->expr()->eq('t.id', ':id'))
            ->setParameter('id', $transportId)
            ->setFirstResult(0)
            ->setMaxResults(1);

        $date = $qb->getQuery()->getSingleScalarResult();

        if (!$date) {
            return null;
        }

        return new \DateTime($date, new \DateTimeZone('UTC'));
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->registry->getRepository($this->context->getClassName());
    }
}
