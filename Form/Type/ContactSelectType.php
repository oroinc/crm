<?php
namespace Oro\Bundle\ContactBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'OroContactBundle:Contact',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createFlexibleQueryBuilder('c', array('first_name', 'last_name'));
                },
                'empty_value' => 'Choose a contact...',
                'empty_data'  => null
            )
        );
    }

    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_contact_select';
    }
}
