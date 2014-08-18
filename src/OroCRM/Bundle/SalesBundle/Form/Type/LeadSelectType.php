<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadSelectType extends AbstractType
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'leads',
                'create_form_route'  => 'orocrm_sales_lead_create',
                'entity_class'       => $this->className,
                'configs'            => [
                    'placeholder' => 'orocrm.sales.form.choose_lead'
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_select';
    }
}
