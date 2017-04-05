<?php

namespace Oro\Bundle\MagentoBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\SalesBundle\Form\Type\OpportunityType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\MagentoBundle\Customer\AssociationChecker;

/**
 * @deprecated since 2.0. This class will not be used.
 */
class OpportunityCustomerAssociationExtension extends AbstractTypeExtension
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AssociationChecker */
    protected $checker;

    /**
     * @param DoctrineHelper     $doctrineHelper
     * @param AssociationChecker $checker
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AssociationChecker $checker
    ) {
        $this->checker = $checker;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if (empty($data['customerAssociation']) || !is_string($data['customerAssociation'])) {
                    return;
                }
                $value = json_decode($data['customerAssociation'], true);
                if (!is_array($value) ||
                    empty($value['entityClass']) ||
                    empty($value['entityId']) ||
                    $value['entityClass'] !== MagentoCustomer::class
                ) {
                    return;
                }
                $magentoCustomer = $this->getMagentoCustomer((int)$value['entityId']);

                if ($magentoCustomer) {
                    // If Magento Customer do not have yet customer association we need to manually create it
                    $this->checker->fixAssociation($magentoCustomer);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OpportunityType::NAME;
    }

    /**
     * @param int $id
     *
     * @return MagentoCustomer|null
     */
    protected function getMagentoCustomer($id)
    {
        return $this->doctrineHelper
            ->getEntityRepositoryForClass(MagentoCustomer::class)
            ->findOneBy(['id' => $id]);
    }
}
