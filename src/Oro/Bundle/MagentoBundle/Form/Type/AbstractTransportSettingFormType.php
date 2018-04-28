<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\IsDisplayOrderNotesSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SharedEmailListSubscriber;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTransportSettingFormType extends AbstractType
{
    /** @var MagentoTransportInterface */
    const NAME = 'oro_magento_transport_setting_form_type';
    const SHARED_GUEST_EMAIL_FIELD_NAME = 'sharedGuestEmailList';
    const IS_DISPLAY_ORDER_NOTES_FIELD_NAME = 'isDisplayOrderNotes';

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var TypesRegistry */
    protected $registry;

    /** @var SettingsFormSubscriber */
    protected $subscriber;

    /**
     * @param MagentoTransportInterface $transport
     * @param SettingsFormSubscriber $subscriber
     * @param TypesRegistry $registry
     */
    public function __construct(
        MagentoTransportInterface $transport,
        SettingsFormSubscriber $subscriber,
        TypesRegistry $registry
    ) {
        $this->transport  = $transport;
        $this->subscriber = $subscriber;
        $this->registry   = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber($this->subscriber)
            ->addEventSubscriber(new SharedEmailListSubscriber())
            ->addEventSubscriber(new IsDisplayOrderNotesSubscriber())
        ;

        $builder->add(
            'apiUrl',
            TextType::class,
            ['label' => '', 'required' => true]
        );

        $builder->add(
            'apiUser',
            TextType::class,
            ['label' => '', 'required' => true]
        );

        $builder->add(
            'apiKey',
            PasswordType::class
        );

        $builder->add(
            'guestCustomerSync',
            CheckboxType::class,
            [
                'label' => 'oro.magento.magentotransport.guest_customer_sync.label',
                'tooltip' => 'oro.magento.magentotransport.guest_customer_sync.tooltip',
                'required' => false
            ]
        );

        $builder->add(
            'syncStartDate',
            OroDateType::class,
            [
                'label'      => 'oro.magento.magentotransport.sync_start_date.label',
                'required'   => true,
                'tooltip'    => 'oro.magento.magentotransport.sync_start_date.tooltip',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            ]
        );

        $builder->add(
            'check',
            TransportCheckButtonType::class,
            [
                'label' => 'oro.magento.magentotransport.check_connection.label'
            ]
        );

        $builder->add(
            'websiteId',
            WebsiteSelectType::class,
            [
                'label'    => 'oro.magento.magentotransport.website_id.label',
                'required' => true
            ]
        );

        $builder->add(
            $builder->create('websites', HiddenType::class)
                ->addViewTransformer(new ArrayToJsonTransformer())
        );

        $builder->add(
            $builder->create(
                self::IS_DISPLAY_ORDER_NOTES_FIELD_NAME,
                IsDisplayOrderNotesFormType::class
            )
        );

        $builder->add(
            $builder->create(
                self::SHARED_GUEST_EMAIL_FIELD_NAME,
                SharedGuestEmailListType::class
            )
        );

        $builder->add(
            $builder
                ->create('isExtensionInstalled', HiddenType::class)
                ->addEventSubscriber(new ConnectorsFormSubscriber($this->registry))
        );

        $builder->add('magentoVersion', HiddenType::class)
            ->add('extensionVersion', HiddenType::class)
            ->add('isOrderNoteSupportExtensionVersion', HiddenType::class, ['mapped' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->transport->getSettingsEntityFQCN()]);
    }

    /**
     * {@inheritdoc}
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
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /**
         * @var $transport MagentoTransport
         */
        $transportEntity = $form->getData();
        if ($transportEntity instanceof MagentoTransport && $transportEntity->getId()) {
            // todo: use transport entity to get required values. It will be changed in scope of CRM-8339
            $isExtensionInstalled = $transportEntity->getIsExtensionInstalled();
            $extensionVersion = $transportEntity->getExtensionVersion();

            $isSupportExtensionVersion = $isExtensionInstalled &&
                version_compare(
                    $transportEntity->getExtensionVersion(),
                    $this->transport->getRequiredExtensionVersion(),
                    'ge'
                );

            $isSupportedOrderNoteExtensionVersion = $isExtensionInstalled &&
                version_compare(
                    $transportEntity->getExtensionVersion(),
                    $this->transport->getOrderNoteRequiredExtensionVersion(),
                    'ge'
                );

            $view->vars['oroBridgeExtension'] = [
                'isExtensionInstalled' => $isExtensionInstalled,
                'isSupportExtensionVersion' => $isSupportExtensionVersion,
                'isOrderNoteSupportExtensionVersion' => $isSupportedOrderNoteExtensionVersion,
                'extensionVersion' => $extensionVersion
            ];
        }
    }
}
