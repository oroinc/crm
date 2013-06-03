<?php
namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountSelectType extends AbstractType
{
    /**
     * @var EntityToIdTransformer
     */
    protected $transformer;

    public function __construct(EntityManager $em)
    {
        $this->transformer = new EntityToIdTransformer($em, 'OroCRMAccountBundle:Account');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'autocompleter_alias' => 'test',
                'configs' => array(
                    'placeholder' => 'Choose an account...',
                    'datasource' => 'grid',
                    'route' => 'orocrm_account_index',
                    'grid' => array(
                        'name' => 'accounts',
                        'property' => 'name'
                    )
                ),
                'transformer' => $this->transformer
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
