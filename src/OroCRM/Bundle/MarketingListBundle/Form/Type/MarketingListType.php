<?php

namespace OroCRM\Bundle\MarketingListBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\QueryDesignerBundle\Form\Type\AbstractQueryDesignerType;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType as MarketingListTypeEntity;

class MarketingListType extends AbstractQueryDesignerType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['required' => true])
            ->add('entity', 'orocrm_marketing_list_contact_information_entity_choice', ['required' => true])
            ->add('description', 'oro_resizeable_rich_text', ['required' => false]);

        // TODO: remove this listener after full support of manual marketing lists CRM-1878
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var MarketingList $marketingList */
                $marketingList = $event->getData();
                $form = $event->getForm();
                if ($marketingList && $marketingList->getId() && $marketingList->isManual()) {
                    $qb = function (EntityRepository $er) {
                        return $er->createQueryBuilder('mlt')
                            ->andWhere('mlt.name = :manualTypeName')
                            ->setParameter('manualTypeName', MarketingListTypeEntity::TYPE_MANUAL);
                    };
                } else {
                    $qb = function (EntityRepository $er) {
                        return $er->createQueryBuilder('mlt')
                            ->andWhere('mlt.name != :manualTypeName')
                            ->setParameter('manualTypeName', MarketingListTypeEntity::TYPE_MANUAL)
                            ->addOrderBy('mlt.name', 'ASC');
                    };
                }

                $form->add(
                    'type',
                    'entity',
                    [
                        'class' => 'OroCRMMarketingListBundle:MarketingListType',
                        'property' => 'label',
                        'required' => true,
                        'empty_value' => 'orocrm.marketinglist.form.choose_marketing_list_type',
                        'query_builder' => $qb
                    ]
                );
            }
        );

        parent::buildForm($builder, $options);
    }

    /**
     * Gets the default options for this type.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'column_column_choice_type' => 'hidden',
            'filter_column_choice_type' => 'oro_entity_field_select'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $options = array_merge(
            $this->getDefaultOptions(),
            [
                'data_class' => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList',
                'intention' => 'marketing_list',
                'cascade_validation' => true
            ]
        );

        $resolver->setDefaults($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_marketing_list';
    }
}
