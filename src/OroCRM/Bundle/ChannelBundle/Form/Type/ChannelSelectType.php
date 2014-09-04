<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ChannelSelectType extends AbstractType
{
    const NAME = 'orocrm_channel_select_type';

    /** @var EntityManager */
    protected $em;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

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
        $queryBuilderNormalizer = function (Options $options, $qb) {

            /** @var EntityManager $em */
            $em = $options['em'];

            $repository = $em->getRepository('OroCRMChannelBundle:Channel');
            $entities   = $options['configs']['entities'];

            return $qb($repository, $entities);
        };

        $resolver->setDefaults(
            [
                'label'         => 'orocrm.channel.entity_label',
                'class'         => 'OroCRMChannelBundle:Channel',
                'property'      => 'name',
                'random_id'     => true,
                'query_builder' => function (EntityRepository $er, $entities = null) {
                    $query = $er->createQueryBuilder('c');

                    if (!empty($entities)) {
                        $query->innerJoin('c.entities', 'e');
                        $query->andWhere($query->expr()->in('e.name', $entities));
                    }

                    $query->orderBy('c.name', 'ASC');

                    if (!empty($entities)) {
                        $query->groupBy('c.name');
                        $query->having(
                            $query->expr()->eq(
                                $query->expr()->countDistinct('e.name'),
                                ':count'
                            )
                        );

                        $query->setParameter('count', count($entities));
                    }
                    $query->andWhere('c.status = :status');
                    $query->setParameter('status', Channel::STATUS_ACTIVE);

                    return $query;
                    },
                'configs'       => [
                    'allowClear'  => true,
                    'placeholder' => 'orocrm.channel.form.select_channel_type.label'
                ],
            ]
        );

        $resolver->setNormalizers(['query_builder' => $queryBuilderNormalizer]);
    }
}
