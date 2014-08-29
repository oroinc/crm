<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSelectType extends AbstractType
{
    const NAME = 'orocrm_channel_select_type';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'label'         => 'orocrm.channel.entity_label',
                'class'         => 'OroCRMChannelBundle:Channel',
                'property'      => 'name',
                'random_id'     => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.status = '.Channel::STATUS_ACTIVE)
                        ->orderBy('c.name', 'ASC');
                        #->setParameter('status', Channel::STATUS_ACTIVE);
                },
                'configs'       => [
                    'allowClear'  => true,
                    'placeholder' => 'orocrm.channel.form.select_channel_type.label'
                ],
            ]
        );
    }
}
