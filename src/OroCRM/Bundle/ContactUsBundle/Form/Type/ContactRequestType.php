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
            ->add('channel', $options['channel_form_type'], [
                    'class' => 'OroIntegrationBundle:Channel',
                    'property' => 'name',
                ])
            ->add('Submit', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
                'channel_form_type' => 'entity'
            ]
        );
    }
}
