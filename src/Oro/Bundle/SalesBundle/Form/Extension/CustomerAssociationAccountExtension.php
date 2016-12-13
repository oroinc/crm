<?php

namespace Oro\Bundle\SalesBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class CustomerAssociationAccountExtension extends AbstractTypeExtension
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var AccountCustomerManager */
    protected $manager;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param ConfigProvider         $customerConfigProvider
     * @param AccountCustomerManager $manager
     * @param DoctrineHelper         $doctrineHelper
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        AccountCustomerManager $manager,
        DoctrineHelper $doctrineHelper
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->manager                = $manager;
        $this->doctrineHelper         = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['customer_association_disabled']) {
            return;
        }

        $formConfig = $builder->getFormConfig();

        if (!$formConfig->getCompound()) {
            return;
        }

        $dataClassName = $formConfig->getDataClass();
        if (!$dataClassName || !$this->customerConfigProvider->isCustomerClass($dataClassName)) {
            return;
        }

        $builder->add(
            'customer_association_account',
            'oro_account_select',
            [
                'required'    => true,
                'label'       => 'oro.account.entity_label',
                'mapped'      => false,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $target   = $event->getData();
                if (!$target || $this->doctrineHelper->isNewEntity($target)) {
                    return;
                }
                $customer = $this->manager->getAccountCustomerByTarget($target);
                $event->getForm()->get('customer_association_account')->setData($customer->getAccount());
            }
        );
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $target   = $event->getData();
                $account  = $event->getForm()->get('customer_association_account')->getData();
                if (!$this->doctrineHelper->isNewEntity($target)) {
                    $customer = $this->manager->getAccountCustomerByTarget($target);
                    $customer->setTarget($account, $target);
                } else {
                    $customer = AccountCustomerManager::createCustomer($account, $target);
                    $this->doctrineHelper->getEntityManager($customer)->persist($customer);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'customer_association_disabled' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }
}
