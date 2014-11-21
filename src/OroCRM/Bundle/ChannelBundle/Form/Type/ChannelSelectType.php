<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * Class ChannelSelectType
 *
 * @see Resourses\Doc\ChannelSelectType.md
 */
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
        $queryBuilderNormalizer = function (Options $options, $qb) {
            /** @var EntityManager $em */
            $em = $options['em'];

            /** @var EntityRepository $repository */
            $repository = $em->getRepository('OroCRMChannelBundle:Channel');
            $entities   = $options->get('entities');

            return $qb($repository, $entities);
        };

        $resolver->setDefaults(
            [
                'label'         => 'orocrm.channel.entity_label',
                'class'         => 'OroCRMChannelBundle:Channel',
                'property'      => 'name',
                'random_id'     => true,
                'query_builder' => $this->getQueryBuilder(),
                'configs'       => [
                    'allowClear'  => true,
                    'placeholder' => 'orocrm.channel.form.select_channel_type.label'
                ],
                'entities'      => [],
                'translatable_options' => false
            ]
        );

        $resolver->setNormalizers(['query_builder' => $queryBuilderNormalizer]);
    }

    /**
     * @return callable
     */
    private function getQueryBuilder()
    {
        return function (EntityRepository $er, $entities = null) {
            $query = $er->createQueryBuilder('c');

            if (!empty($entities)) {
                $countDistinctName = $query->expr()->eq($query->expr()->countDistinct('e.name'), ':count');

                $query->innerJoin('c.entities', 'e');
                $query->andWhere($query->expr()->in('e.name', $entities));
                $query->groupBy('c.name', 'c.id');
                $query->having($countDistinctName);
                $query->setParameter('count', count($entities));
            }

            $query->andWhere('c.status = :status');
            $query->orderBy('c.name', 'ASC');
            $query->setParameter('status', Channel::STATUS_ACTIVE);

            return $query;
        };
    }
}
