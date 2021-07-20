<?php

namespace Oro\Bundle\SalesBundle\Model;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeLeadStatus
{
    const STATUS_QUALIFY    = 'qualified';
    const STATUS_DISQUALIFY = 'canceled';

    /** @var EntityManager */
    protected $manager;

    /** @var ValidatorInterface */
    protected $validator;

    public function __construct(EntityManager $manager, ValidatorInterface $validator)
    {
        $this->manager   = $manager;
        $this->validator = $validator;
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function disqualify(Lead $lead)
    {
        return $this->changeStatus($lead, self::STATUS_DISQUALIFY);
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function qualify(Lead $lead)
    {
        return $this->changeStatus($lead, self::STATUS_QUALIFY);
    }

    /**
     * @param Lead   $lead
     * @param string $statusCode
     *
     * @return bool
     */
    protected function changeStatus($lead, $statusCode)
    {
        try {
            $enumStatusClass = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);
            $status          = $this->manager->getReference($enumStatusClass, $statusCode);
            $lead->setStatus($status);

            $errors = $this->validator->validate($lead);
            if ($errors->count()) {
                return false;
            }
            $this->save($lead);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    protected function save(Lead $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
