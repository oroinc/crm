<?php
namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'Choose an account...',
                    'datasource' => 'grid',
                    'route' => 'orocrm_account_index',
                    'grid' => array(
                        'name' => 'accounts',
                        'property' => 'name'
                    )
                ),
                'autocompleter_alias' => 'test',
                'class' => 'OroCRMAccountBundle:Account'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account_select';
    }
}
