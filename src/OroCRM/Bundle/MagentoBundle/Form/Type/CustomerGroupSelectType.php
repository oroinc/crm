<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomerGroupSelectType extends AbstractType
{
    const NAME = 'orocrm_magento_customer_group_select';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'magento_customer_group',
                'grid_name' => 'magento-customers-group-by-channel-grid',
                'configs' => [
                    'placeholder' => 'orocrm.magento.customergroup.placeholder'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline_channel_aware';
    }
}
