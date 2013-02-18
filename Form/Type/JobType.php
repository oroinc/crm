<?php
namespace Oro\Bundle\DataFlowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\DataFlowBundle\Entity\Connector;
use Oro\Bundle\DataFlowBundle\Form\DataTransformer\EntityToConfigurationTransformer;

/**
 * Base jon type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class JobType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('description', 'text', array('required' => true));

        $configurationType = $options['configuration_type'];
        $builder->add($builder->create('configuration', $configurationType));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Oro\Bundle\DataFlowBundle\Entity\Job'));
        $resolver->setRequired(array('configuration_type'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_dataflow_job';
    }
}
