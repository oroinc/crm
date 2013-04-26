<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\AddValueFieldSubscriber;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

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
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var string
     */
    protected $valueClass;

    /**
     * Constructor
     *
     * @param FlexibleManager $flexibleManager the manager
     */
    public function __construct(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;
        $this->valueClass      = $flexibleManager->getFlexibleValueName();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $this->addSubscriber($builder);
    }

    /**
     * Add subscriber
     *
     * @param FormBuilderInterface $builder
     */
    public function addSubscriber(FormBuilderInterface $builder)
    {
        $subscriber = new AddValueFieldSubscriber($builder->getFormFactory(), $this->flexibleManager);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => $this->valueClass));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_value';
    }
}
