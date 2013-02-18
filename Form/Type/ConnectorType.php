<?php
namespace Oro\Bundle\DataFlowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\DataFlowBundle\Entity\Connector;

/**
 * Base connector type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConnectorType extends AbstractType
{

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var string
     */
    protected $configurationType;

    /**
     * Constructor
     *
     * @param Connector $connector
     * @param string    $confFormType
     *
    public function __construct(Connector $connector, $configurationType)
    {
        $this->connector = $connector;
        $this->configurationType = $configurationType;
    }*/

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
*/
        $configurationType = $options['configuration_type'];

        $builder->add('id', 'hidden');
        $builder->add('description', 'text', array('required' => true));


        $connector = $options['data'];
        $configuration = $connector->getConfiguration()->deserialize();
        $configuration->setId($connector->getConfiguration()->getId());

        //$transformer = new ConfigurationToEntityTransformer();

        $builder
            ->add('configuration', new $configurationType(), array('data' => $configuration, 'property_path' => false))
;//            ->addModelTransformer($transformer);

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Oro\Bundle\DataFlowBundle\Entity\Connector'));
        $resolver->setRequired(array('configuration_type'));
//        $resolver->setAllowedTypes(array('em' => 'Doctrine\Common\Persistence\ObjectManager'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_dataflow_connector';
    }
}
