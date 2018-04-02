<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreSelectType extends AbstractType
{
    const NAME = 'oro_magento_store_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'magento_store',
                'grid_name' => 'magento-store-by-channel-grid',
                'configs' => [
                    'placeholder' => 'oro.magento.store.placeholder'
                ]
            ]
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CreateOrSelectInlineChannelAwareType::class;
    }
}
