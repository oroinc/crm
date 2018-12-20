<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SoapTransportSettingFormType extends AbstractTransportSettingFormType
{
    const NAME = 'oro_magento_soap_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'apiUrl',
            TextType::class,
            ['label' => 'oro.magento.magentotransport.soap.wsdl_url.label', 'required' => true]
        );
        $builder->add(
            'apiUser',
            TextType::class,
            ['label' => 'oro.magento.magentotransport.soap.api_user.label', 'required' => true]
        );
        $builder->add(
            'apiKey',
            PasswordType::class,
            [
                'label'       => 'oro.magento.magentotransport.soap.api_key.label',
                'required'    => true,
                'constraints' => [new NotBlank()]
            ]
        );
        $builder->add(
            'isWsiMode',
            CheckboxType::class,
            ['label' => 'oro.magento.magentotransport.soap.is_wsi_mode.label', 'required' => false]
        );

        $builder->remove('check');
        $builder->remove('websiteId');
        $builder->remove(self::SHARED_GUEST_EMAIL_FIELD_NAME);
        $builder->remove(self::IS_DISPLAY_ORDER_NOTES_FIELD_NAME);

        // added because of field orders
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
            'adminUrl',
            TextType::class,
            ['label' => 'oro.magento.magentotransport.admin_url.label', 'required' => false]
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
        return self::NAME;
    }
}
