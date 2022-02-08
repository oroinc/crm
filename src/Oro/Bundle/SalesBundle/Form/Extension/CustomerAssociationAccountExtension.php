<?php

namespace Oro\Bundle\SalesBundle\Form\Extension;

use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Adds associated account field for customers form.
 */
class CustomerAssociationAccountExtension extends AbstractTypeExtension implements ServiceSubscriberInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var ContainerInterface */
    private $container;

    public function __construct(DoctrineHelper $doctrineHelper, ContainerInterface $container)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_sales.customer.config_provider' => ConfigProvider::class,
            'oro_sales.manager.account_customer' => AccountCustomerManager::class
        ];
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
        if (!$dataClassName || !$this->isCustomerClass($dataClassName)) {
            return;
        }

        $builder->add(
            'customer_association_account',
            AccountSelectType::class,
            [
                'required' => $this->isAccountRequired($builder->getData()),
                'label'    => 'oro.account.entity_label',
                'mapped'   => false,
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $target = $event->getData();
                if (!$target || $this->doctrineHelper->isNewEntity($target)) {
                    return;
                }

                $customer = $this->getManager()->getAccountCustomerByTarget($target, false);
                if ($customer) {
                    $event->getForm()->get('customer_association_account')->setData($customer->getAccount());
                }
            }
        );
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $this->setAccountForCustomer($event);
            }
        );
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    private function isCustomerClass($className)
    {
        return
            $this->doctrineHelper->isManageableEntityClass($className)
            && $this->getConfigProvider()->isCustomerClass($className);
    }

    /**
     * @param Customer|null $target
     * @return bool
     */
    private function isAccountRequired($target)
    {
        return $target !== null && !$this->doctrineHelper->isNewEntity($target);
    }

    private function setAccountForCustomer(FormEvent $event)
    {
        $target  = $event->getData();
        $account = $event->getForm()->get('customer_association_account')->getData();
        if ($this->doctrineHelper->isNewEntity($target)) {
            $account = $account ?? $this->getManager()->createAccountForTarget($target);
            $customer = new Customer();
            $customer->setTarget($account, $target);
            $this->doctrineHelper->getEntityManager($customer)->persist($customer);

            return;
        }

        if (!$account) {
            return;
        }

        $customer = $this->getManager()->getAccountCustomerByTarget($target, false);
        if ($customer) {
            $customer->setTarget($account, $target);
        } else {
            $customer = new Customer();
            $customer->setTarget($account, $target);
            $this->doctrineHelper->getEntityManager($customer)->persist($customer);
        }
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
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    private function getConfigProvider(): ConfigProvider
    {
        return $this->container->get('oro_sales.customer.config_provider');
    }

    private function getManager(): AccountCustomerManager
    {
        return $this->container->get('oro_sales.manager.account_customer');
    }
}
