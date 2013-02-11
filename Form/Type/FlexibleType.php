<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Base flexible form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleType extends AbstractType
{

    /**
     * @var string
     */
    protected $flexibleClass;

    /**
     * @var string
     */
    protected $valueClass;

    /**
     * Construct with full name of concrete impl of flexible entity class
     *
     * @param string $flexibleClass flexible entity class
     * @param string $valueClass    flexible value class
     */
    public function __construct($flexibleClass, $valueClass)
    {
        $this->flexibleClass = $flexibleClass;
        $this->valueClass = $valueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);
        $this->addDynamicAttributesFields($builder);
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden');
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder)
    {
        $builder->add(
            'values',
            'collection',
            array(
                'type'         => new FlexibleValueType($this->valueClass),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->flexibleClass
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_entity';
    }
}
