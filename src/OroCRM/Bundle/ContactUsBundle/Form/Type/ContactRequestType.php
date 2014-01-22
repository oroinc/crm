<?php
namespace OroCRM\Bundle\ContactUsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contact_request';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')
            ->add('email')
            ->add('phone')
            ->add('comment', 'textarea')
            ->add('channel', 'oro_entity_identifier', [
                    'class' => 'OroIntegrationBundle:Channel',
                    'multiple' => false
                ])
            ->add('Send', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest'
            )
        );
    }
}
