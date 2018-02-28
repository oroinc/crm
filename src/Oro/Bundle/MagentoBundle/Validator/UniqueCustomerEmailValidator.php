<?php

namespace Oro\Bundle\MagentoBundle\Validator;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCustomerEmailValidator extends ConstraintValidator
{
    /**
     * @var TypesRegistry
     */
    protected $typesRegistry;

    /**
     * @param TypesRegistry $typesRegistry
     */
    public function __construct(TypesRegistry $typesRegistry)
    {
        $this->typesRegistry = $typesRegistry;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        /**
         * @var $constraint UniqueCustomerEmailConstraint
         */
        if ($value instanceof Customer) {
            $transportEntity = $value->getChannel()->getTransport();
            $transportProvider = $this->typesRegistry->getTransportTypeBySettingEntity(
                $transportEntity,
                $value->getChannel()->getType()
            );

            if (! $transportProvider instanceof MagentoTransportInterface) {
                throw new UnexpectedTypeException($transportProvider, MagentoTransportInterface::class);
            }

            try {
                $transportProvider->init($transportEntity);
                /**
                 * @var $transportProvider MagentoTransportInterface
                 */
                if (! $transportProvider->isCustomerHasUniqueEmail($value)) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('email')
                        ->addViolation();
                }
            } catch (\RuntimeException $e) {
                $this->context->buildViolation($constraint->transportMessage)
                    ->atPath('email')
                    ->addViolation();
            }
        }
    }
}
