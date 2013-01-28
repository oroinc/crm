<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\AddValueFieldSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Base flexible value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleValueType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        $subscriber = new AddValueFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Acme\Bundle\DemoFlexibleEntityBundle\Entity\CustomerValue'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_value';
    }
}