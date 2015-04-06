<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StoreSelectType extends AbstractType
{
    const NAME = 'orocrm_magento_store_select';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'magento_store',
                'grid_name' => 'magento-store-by-channel-grid',
                'configs' => [
                    'placeholder' => 'orocrm.magento.store.placeholder'
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
