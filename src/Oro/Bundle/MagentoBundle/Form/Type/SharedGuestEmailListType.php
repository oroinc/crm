<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\MagentoBundle\Form\DataTransformer\EmailListToStringTransformer;

class SharedGuestEmailListType extends AbstractType
{
    const NAME = 'oro_magento_shared_guest_email_list_type';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new EmailListToStringTransformer());
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'    => 'oro.magento.magentotransport.shared_guest_email_list.label',
            'required' => false,
            'tooltip'  => 'oro.magento.magentotransport.shared_guest_email_list.tooltip',
            'error_bubbling' => true
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'textarea';
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
