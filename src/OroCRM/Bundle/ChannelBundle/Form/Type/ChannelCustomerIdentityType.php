<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

class ChannelCustomerIdentityType extends AbstractType
{
    const NAME = 'orocrm_channel_customer_identity_select_form';

    /**
     * @var EntityProvider
     */
    protected $provider;

    /**
     * @param EntityProvider $provider
     */
    public function __construct(EntityProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'empty_value' => '',
                'choices'     => $this->getChoices(true),
            ]
        );
    }


    protected function getChoices($showPlural)
    {
        $choices  = [];
        $entities = $this->provider->getEntities($showPlural);

        foreach ($entities as $entity) {
            $attributes = [];
            foreach ($entity as $key => $val) {
                if (!in_array($key, ['name'])) {
                    $attributes['data-' . $key] = $val;
                }
            }
            $choices[$entity['name']] = new ChoiceListItem(
                $showPlural ? $entity['plural_label'] : $entity['label'],
                $attributes
            );
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
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
        return 'genemu_jqueryselect2_choice';
    }
}
