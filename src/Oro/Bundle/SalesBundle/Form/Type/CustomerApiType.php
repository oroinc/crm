<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ApiBundle\Collection\IncludedEntityCollection;
use Oro\Bundle\ApiBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\ApiBundle\Metadata\AssociationMetadata;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerApiTransformer;

class CustomerApiType extends AbstractType
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * @param ManagerRegistry        $doctrine
     * @param AccountCustomerManager $manager
     */
    public function __construct(ManagerRegistry $doctrine, AccountCustomerManager $manager)
    {
        $this->doctrine               = $doctrine;
        $this->accountCustomerManager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AssociationMetadata $metadata */
        $metadata = $options['metadata'];
        /** @var IncludedEntityCollection|null $includedEntities */
        $includedEntities = $options['included_entities'];
        $builder->addViewTransformer(
            new CustomerApiTransformer(
                new EntityToIdTransformer($this->doctrine, $metadata, $includedEntities),
                $this->accountCustomerManager
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['compound' => false, 'included_entities' => null])
            ->setRequired(['metadata'])
            ->setAllowedTypes('metadata', [AssociationMetadata::class])
            ->setAllowedTypes('included_entities', ['null', IncludedEntityCollection::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_sales_customer_api';
    }
}
