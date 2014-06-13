<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class CaseCommentApiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_comment_api';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'orocrm_case_comment';
    }
}
