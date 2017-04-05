<?php

namespace Oro\Bundle\MagentoBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerType;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\MagentoBundle\Customer\AssociationChecker;

/**
 * @deprecated since 2.0. This class will not be used.
 */
class CustomerAssociationExtension extends AbstractTypeExtension
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AssociationChecker */
    protected $checker;

    /**
     * @param DoctrineHelper     $doctrineHelper
     * @param AssociationChecker $checker
     */
    public function __construct(DoctrineHelper $doctrineHelper, AssociationChecker $checker)
    {
        $this->checker        = $checker;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var MagentoCustomer $magentoCustomer */
                $magentoCustomer = $event->getData();
                if (!$magentoCustomer || $this->doctrineHelper->isNewEntity($magentoCustomer)) {
                    return;
                }
                // If Magento Customer do not have yet customer association we need to manually create it
                $this->checker->fixAssociation($magentoCustomer);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerType::NAME;
    }
}
