<?php
namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountSelectType extends AbstractType
{
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
                'empty_value' => '',
                'empty_data'  => null
            )
        );
    }

    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account_select';
    }
}

